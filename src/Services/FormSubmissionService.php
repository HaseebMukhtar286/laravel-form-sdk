<?php

namespace haseebmukhtar286\LaravelFormSdk\Services;

use App\Declarations\Declarations;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use haseebmukhtar286\LaravelFormSdk\Models\FormSubmission;
use haseebmukhtar286\LaravelFormSdk\Models\FormSubmissionHistory;
use haseebmukhtar286\LaravelFormSdk\Models\ReportNumber;
use haseebmukhtar286\LaravelFormSdk\Declarations\Declarations as PackageDeclarations;

class FormSubmissionService
{
    public static function paginate($request)
    {
        $columns = '*';
        if (isset($request->columns)) {
            $requestedColumns = array_filter($request->columns, function ($column) {
                return strpos($column, 'data.') === 0;
            });
            $columns = [...$requestedColumns, 'user_id', 'created_at', 'status', 'report_no'];
        }
    
        $per_page = $request->per_page ? $request->per_page : 20;
        $collection = FormSubmission::select($columns)
            ->where('form_id', $request->id);
    
        // Apply user region relationship if it exists
        if (method_exists(User::class, 'region')) {
            $collection = $collection->with("user.region");
        }
        
        // Include user name, email, and type in the result set
        $collection = $collection->with("user:name,email,type");
    
        // Order by created_at in descending order
        $collection = $collection->orderBy('created_at', 'desc');
    
        // Search logic for both form submission columns and user fields
        if ($request->search) {
            $searchTerm = '%' . trim($request->search) . '%';
    
            $collection->where(function ($query) use ($searchTerm, $columns) {
                // Search within the form data columns (only specific columns, not '*')
                if ($columns != '*') {
                    foreach ($columns as $column) {
                        if (strpos($column, 'data.') === 0) {
                            $query->orWhere($column . '.label', 'LIKE', $searchTerm)
                            ->orWhere($column, 'LIKE', $searchTerm) ;
                        }
                    }
                }
    
                // Search within user name and email fields
                $query->orWhereRelation('user', 'name', 'LIKE', $searchTerm)
                      ->orWhereRelation('user', 'email', 'LIKE', $searchTerm);
            });
        }
    
        // Apply date range filters
        if ($request->fromDate && $request->toDate) {
            $fromDate = Carbon::parse($request->fromDate)->startOfDay();
            $toDate = Carbon::parse($request->toDate)->endOfDay();
            $collection = $collection->whereBetween('created_at', [$fromDate, $toDate]);
        }
    
        // Apply additional filtering based on user role
        if (!auth()->user()->isAdmin()) {
            if (auth()->user()->region_ids && (auth()->user()->isTopThree() || auth()->user()->isClusterManager())) {
                $collection = $collection->whereIn('data.region.value', auth()->user()->region_ids);
            } else {
                $collection = $collection->where("user_id", auth()->user()->_id);
            }
        }
    
        // Paginate the results
        $collection = $collection->paginate(intVal($per_page));
    
        // Clean up user region data before returning
        $collection->getCollection()->transform(function ($item) {
            if (isset($item['user']['region'])) {
                foreach ($item['user']['region'] as $key => $value) {
                    unset($value['user_ids'], $value['phcRegion'], $value['updated_at'], $value['created_at']);
                }
            }
            return $item;
        });
    
        return response()->json(['data' => $collection], 200);
    }
    

    public static function all($request)
    {
        if ($request->id) {
            $collection = FormSubmission::where('form_id', $request->id)->orderBy('created_at', 'dsc');

            if (isset($reques->submissionId)) {
                $collection  =  $collection->where("_id", $request->submissionId);
            }

            $collection = $collection->get();

            $uri = "/form/" . $request->id;

            [$result] = ApiService::makeRequest('GET', $uri);

            return response()->json(['data' => $collection, 'schema' => $result], 200);
        }
        return response()->json(['data' => []], 400);
    }

    public static function find($id)
    {
        $collection = FormSubmission::where('_id', $id)->with('user')
            ->first();
        if (isset($collection)) {
            return response()->json(['data' => $collection], 200);
        } else {
            return response()->json(['data' => []], 400);
        }
    }
    public static function create($request)
    {
        $data  = [
            "form_id" => $request->id,
            "data" => $request->data,
            "user_id" => auth()->user()->_id,
            "schema_version" => $request->schema_version ? $request->schema_version : '',
            "report_no" => (string) self::generateReportNo(),
        ];
        if (auth()->user()->type != 'facility') {
            $data['status'] = PackageDeclarations::ALL_STATUS['APPROVED'];
        } else {
            $site_id = isset($request->data['site']['value']) ? $request->data['site']['value'] : '';
            $getSubmissions =  FormSubmission::where('form_id', $request->id)->where('user_id', auth()->user()->_id)->where('data.site.value', $site_id)->where('created_at', '>=', Carbon::now()->subMonths(3)->startOfDay())->get();
            if (count($getSubmissions) > 0) {
                return response()->json(['data' => "You have already made a submission. You cannot submit again for the next three months."], 404);
            }
        }

        $submission = FormSubmission::create($data);
        if (function_exists('afterFormSubmissionCreate')) {
            afterFormSubmissionCreate($submission);
        }
        if (!$submission) return response()->json(['data' => "Submisson not created"], 402);

        return response()->json(['data' => 'Submission Successfully created'], 200);
    }

    //update the ReportNumber model
    private static function generateReportNo()
    {
        $reportCountValue = 1;
        $result = ReportNumber::first();
        if ($result) {
            $reportCountValue = (int)$result->reportCount + 1;
            $result->reportCount = $reportCountValue;
            $result->save();
        } else {
            ReportNumber::create(['reportCount' => $reportCountValue]);
        }
        return $reportCountValue;
    }


    public static function update($request)
    {

        if (FormSubmission::doesntExist('_id', $request->id)) return response()->json(['data' => []], 400);
        FormSubmission::where('_id', $request->id)->update([
            "data" => $request->data
        ]);
        if (function_exists('afterFormSubmissionUpdate')) {
            afterFormSubmissionUpdate($request->id);
        }
        return response()->json(['data' => 'Submission Successfully Updated'], 200);
    }

    public static function destroy($id)
    {
        $collection = FormSubmission::find($id);

        if ($collection) $collection->delete();

        if (function_exists('afterFormSubmissionDelete')) {
            afterFormSubmissionDelete($id);
        }
        return response()->json(['data' => 'Submission Successfully Deleted'], 200);
    }


    public static function dashboard($request)
    {
        $id = "6537c75471a5dba8ab0fdc7a"; // will change it after apprval thi is for moc
        $sevenDaysAgo = Carbon::now()->subDays(7);
        $dataArr['physician_assessment'] = [
            'title' => "Median time interval from door to physician",
            "value" => null,
            "color" => "green",
            "target" => 15,
        ];
        $dataArr["ct_scan"] = [
            'title' => "Median time interval from door to CAT scan ",
            "value" => null,
            "color" => "green",
            "target" => 20,
        ];
        $dataArr["thrombolysis_administration"] = [
            'title' => "Median time interval from door to IV thrombolysis",
            "value" => null,
            "color" => "green",
            "target" => 60,
        ];
        $dataArr["admitted_user"] = [
            'title' => "Proportion of acute stroke patients admitted directly to an acute stroke unit from ED",
            "value" => 0,
            "color" => "green",
            "target" => 80,
        ];

        $collection = FormSubmission::where('form_id', $id)
            ->whereBetween('created_at', [$sevenDaysAgo, now()])
            ->get();

        foreach ($collection as $value) {
            if (isset($value['data']['stroke_time']) && isset($value['data']['triage_time'])) {

                $strokeTime = new DateTime($value['data']['stroke_time']);
                $triageTime = new DateTime($value['data']['triage_time']);

                if (isset($value['data']['physician_time'])) {
                    $physicianTime = new DateTime($value['data']['physician_time']);
                    $timePhyDiffMilliseconds = $physicianTime->getTimestamp() - $triageTime->getTimestamp();
                    $dataArr['physician_assessment']['value'] += $timePhyDiffMilliseconds * 1000;
                }

                if (isset($value['data']['ct_scan_time'])) {
                    $ctScanTime = new DateTime($value['data']['ct_scan_time']);
                    $timeCtScanDiffMilliseconds = $ctScanTime->getTimestamp() - $triageTime->getTimestamp();
                    $dataArr['ct_scan']['value'] += $timeCtScanDiffMilliseconds * 1000;
                }

                if (isset($value['data']['thrombolysis_administration_time'])) {
                    $thrombolysisAdministrationTime = new DateTime($value['data']['thrombolysis_administration_time']);
                    $timeAdministrationDiffMilliseconds = $thrombolysisAdministrationTime->getTimestamp() - $triageTime->getTimestamp();
                    $dataArr['thrombolysis_administration']['value'] += $timeAdministrationDiffMilliseconds * 1000;
                }
                if (isset($value['data']['gtlj46fcvgj']) && $value['data']['gtlj46fcvgj'] == "For Addmission") {
                    $dataArr['admitted_user']['value'] += 1;
                }
            }
        }
        $finalDataArr = [];
        foreach ($dataArr as $key => $data) {
            if ($key == "admitted_user") {
                if (count($collection) > 0) {
                    $result  = ($data['value'] / count($collection)) * 100;
                    $dataArr[$key]['color'] = $result <= $data['target'] ? 'red' : 'green';
                    $dataArr[$key]['value'] = $result . ' %';
                }
            } else {
                $dataArr[$key]['value'] = self::timeFormat($data['value']);
                list($hours, $minutes) = explode(':', $dataArr[$key]['value']);

                // Convert hours to minutes and add to the total minutes
                $totalMinutes = ($hours * 60) + $minutes;

                $dataArr[$key]['color'] = $totalMinutes >= $data['target'] ? 'red' : 'green';
            }
            array_push($finalDataArr, $dataArr[$key]);
        }
        return response()->json(['data' =>  $finalDataArr], 200);
    }
    public static function timeFormat($totalTimeDifferenceMilliseconds)
    {
        // Set a default formatted time
        $formattedTime = "00:00";

        if ($totalTimeDifferenceMilliseconds > 0) {
            $totalMinutes = $totalTimeDifferenceMilliseconds / (1000 * 60);
            $formulaTime = ($totalMinutes + 1) / 2;
            $totalSeconds = $formulaTime * 60;

            // Use gmdate to format seconds into "HH:MM:SS"
            $formattedTime = gmdate("H:i", $totalSeconds);
        }
        return $formattedTime;
    }

    public static function approve($request)
    {
        $collection = FormSubmission::where('_id', $request->id)->first();
        if (isset($collection)) {
            $status =  PackageDeclarations::ALL_STATUS['APPROVED'];
            $data  = [
                "form_id" => $request->id,
                "reason" => $request->reason ?? $request->reason,
                "user_id" => auth()->user()->_id,
                'status' => $status
            ];
            FormSubmissionHistory::create($data);
            $collection->status = $status;
            $collection->save();
            return response()->json(['data' => 'Submission ' . $status . ' Successfully'], 200);
        } else {
            return response()->json(['data' => ['Form Not Found']], 400);
        }
    }

    public static function reject($request)
    {
        $collection = FormSubmission::where('_id', $request->id)->first();
        if (isset($collection)) {
            $status =  PackageDeclarations::ALL_STATUS['REJECTED'];
            $data  = [
                "form_id" => $request->id,
                "reason" => $request->reason ?? $request->reason,
                "user_id" => auth()->user()->_id,
                'status' => $status
            ];
            FormSubmissionHistory::create($data);
            $collection->status = $status;
            $collection->save();
            return response()->json(['data' => 'Submission ' . $status . ' Successfully'], 200);
        } else {
            return response()->json(['data' => ['Form Not Found']], 400);
        }
    }
}
