<?php

namespace haseebmukhtar286\LaravelFormSdk\Services;

use App\Models\User;
use App\Models\TaskSchedule;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Arr;
use haseebmukhtar286\LaravelFormSdk\Models\FormSubmission;
use haseebmukhtar286\LaravelFormSdk\Models\FormSubmissionHistory;
use haseebmukhtar286\LaravelFormSdk\Models\ReportNumber;
use haseebmukhtar286\LaravelFormSdk\Declarations\Declarations as PackageDeclarations;
use haseebmukhtar286\LaravelFormSdk\Models\ObligationSites;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class FormSubmissionService
{
    public static function paginate($request)
    {

        $architecture = $request->architecture ?? "standard";
        $columns = '*';
        if (isset($request->columns)) {
            $requestedColumns = array_filter($request->columns, function ($column) {
                return strpos($column, 'data.') === 0;
            });
            $columns = [...$requestedColumns, 'user_id', 'created_at', 'status', 'report_no', "support_ids", "inspection_type", "form_id","arrest_form_submission_id","arrest_form_submitted_date"];
        }

        $per_page = $request->per_page ? $request->per_page : 20;
        $page = $request->page;
        $collection = FormSubmission::select($columns);

        if (isset($request->user_id) && !empty($request->user_id) && auth()->user()->isAdmin()) {
            $collection = $collection->where('user_id', $request->user_id);
        } else {
            if(isset( $request->id)){
                $collection = $collection->where('form_id', $request->id);
            }
        }



        // Apply user region relationship if it exists
        if (method_exists(User::class, 'region')) {
            $collection = $collection->with(["user.region" => function ($query) {
                return $query->select('name', 'user_ids');
            }]);
        }

        // Include user name, email, and type in the result set
        $collection = $collection->with("user:name,email,type")->with('site:facilityType');

        // Order by created_at in descending order
        $collection = $collection->orderBy('created_at', 'desc');

        if (isset($request->site_id) && !empty($request->site_id)) {
            $collection = $collection->where('data.site.value', $request->site_id);
        }

        // Search logic for both form submission columns and user fields
        if ($request->search) {

            $allForms = collect(Cache::get("AllDynamicForms"));

            $allFormsIds = $allForms->filter(function ($form) use ($request) {
                return stripos($form['name'] ?? '', trim($request->search)) !== false ||
                    stripos($form['meta_data']['name_en'] ?? '', trim($request->search)) !== false;
            })->pluck('_id');

            $searchTerm = '%' . trim($request->search) . '%';

            $collection->where(function ($query) use ($searchTerm, $columns, $request,  $allFormsIds) {
                // Search within the form data columns (only specific columns, not '*')
                if ($columns != '*') {
                    foreach ($columns as $column) {
                        if (strpos($column, 'data.') === 0) {
                            $query->orWhere($column . '.label', 'LIKE', $searchTerm)
                                ->orWhere($column, 'LIKE', $searchTerm);
                        }
                    }
                }

                // Search within user name and email fields
                $query->orWhereRelation('user', 'name', 'LIKE', $searchTerm)
                    ->orWhereRelation('user', 'email', 'LIKE', $searchTerm);

                $query->orWhereRelation('site', 'facilityType', 'LIKE', $searchTerm);

                $query->orWhere('report_no', 'LIKE', $searchTerm);
                $query->orWhere('report_no', (int) trim($request->search));

                if (count($allFormsIds) > 0) {
                    $query->orWhereIn('form_id', $allFormsIds);
                }
            });
        }

        // Apply date range filters
        if ($request->fromDate && $request->toDate) {
            $fromDate = Carbon::parse($request->fromDate)->startOfDay();
            $toDate = Carbon::parse($request->toDate)->endOfDay();
            $collection = $collection->whereBetween('created_at', [$fromDate, $toDate]);
        }

        // Apply additional filtering based on user role
        if (!auth()->user()->isAdmin() && !auth()->user()->isSofAdmin()) {
            if (auth()->user()->region_ids && (auth()->user()->isTopThree())) {
                $collection = $collection->whereIn('data.region.value', auth()->user()->region_ids);
            } elseif (auth()->user()->cluster_ids && (auth()->user()->isClusterManager() || auth()->user()->isHoldCo() || auth()->user()->isSofCluster() || auth()->user()->isSofHoldCo())) {
                $collection = $collection->whereIn('data.cluster.value', auth()->user()->cluster_ids);
            } elseif (auth()->user()->obligation_sites_ids && auth()->user()->isFacilityManager()) {
                $collection = $collection->whereIn('data.site.value', auth()->user()->obligation_sites_ids);
            } else {
                $collection = $collection->where("user_id", auth()->user()->_id);
            }
        }

        $clonsCollection =  $collection->clone();

        // Paginate the results
        $collection = $collection->paginate(intVal($per_page));

        if ($architecture === "standard") {
            $firstSubmissionId = null;
            $lastSubmissionId = null;
            if (isset($request->site_id) && !empty($request->site_id)) {
                $site_id            =   $request->site_id;
                $firstSubmissionId  =   FormSubmission::where('data.site.value', $site_id)->where('form_id', $request->id)->orderBy('created_at', 'asc')->value('_id');
                $lastSubmissionId   =   FormSubmission::where('data.site.value', $site_id)->where('form_id', $request->id)->orderBy('created_at', 'desc')->value('_id');
            }
            return response()->json(['data' => $collection, 'firstSubmissionId' => $firstSubmissionId, 'lastSubmissionId' => $lastSubmissionId], 200);
        }

        $site_ids = $clonsCollection->pluck('data.site.value')->unique()->values();
        $paginatedIds = $site_ids->forPage(intVal($page), intVal($per_page));
        $sites = ObligationSites::whereIn('_id', $paginatedIds)->with("region:name")->get();
        $sites = $paginatedIds->map(function ($id) use ($sites) {
            return $sites->firstWhere('_id', $id);
        });

        $paginated = new LengthAwarePaginator(
            $sites,
            $site_ids->count(),
            intVal($per_page),
            intVal($page),
            ['path' => request()->url(), 'query' => request()->query()]
        );
        return response()->json(['data' => $collection, "dataNew" => $paginated,], 200);
    }


    public static function all($request)
    {
        if ($request->id) {

            $collection = FormSubmission::where('form_id', $request->id)->with(
                [
                    "user:name,email,phone",
                    'user.region' => function ($query) {
                        $query->select('name', 'user_ids');
                    }
                ]

            )->orderBy('created_at', 'dsc');


            if (isset($reques->submissionId)) {
                $collection  =  $collection->where("_id", $request->submissionId);
            }

            if (!auth()->user()->isAdmin() && !auth()->user()->isSofAdmin()) {
                if (auth()->user()->region_ids) {
                    $collection = $collection->whereIn('data.region.value', auth()->user()->region_ids);
                } elseif (auth()->user()->cluster_ids && (auth()->user()->isClusterManager())) {
                    $collection = $collection->whereIn('data.cluster.value', auth()->user()->cluster_ids);
                } else {
                    $collection = $collection->where("user_id", auth()->user()->_id);
                }
            }

            $columns = '*';
            if (isset($request->columns)) {
                $requestedColumns = array_filter($request->columns, function ($column) {
                    return strpos($column, 'data.') === 0;
                });
                $columns = [...$requestedColumns, 'user_id', 'created_at', 'status', 'report_no', "support_ids"];
            }

            if ($request->search) {
                $searchTerm = '%' . trim($request->search) . '%';

                $collection->where(function ($query) use ($searchTerm, $columns) {
                    // Search within the form data columns (only specific columns, not '*')
                    if ($columns != '*') {
                        foreach ($columns as $column) {
                            if (strpos($column, 'data.') === 0) {
                                $query->orWhere($column . '.label', 'LIKE', $searchTerm)
                                    ->orWhere($column, 'LIKE', $searchTerm);
                            }
                        }
                    }

                    // Search within user name and email fields
                    $query->orWhereRelation('user', 'name', 'LIKE', $searchTerm)
                        ->orWhereRelation('user', 'email', 'LIKE', $searchTerm);

                    $query->orWhere('report_no', 'LIKE', $searchTerm);
                });
            }

            // Apply date range filters
            if ($request->fromDate && $request->toDate) {
                $fromDate = Carbon::parse($request->fromDate)->startOfDay();
                $toDate = Carbon::parse($request->toDate)->endOfDay();
                $collection = $collection->whereBetween('created_at', [$fromDate, $toDate]);
            } else {
                // Default to last 3 months if no date range is provided
                $fromDate = Carbon::now()->subMonths(1)->startOfDay();
                $toDate = Carbon::now()->endOfDay();
                $collection = $collection->whereBetween('created_at', [$fromDate, $toDate]);
            }



            $collection = $collection->get();
            $submissionIds = $collection->pluck('_id')->toArray();
            $formatted = [];

            try {
                $userWithReportNo = TaskSchedule::whereIn('submission_id', $submissionIds)
                    ->select("submission", "submission_id")
                    ->with(["submission:report_no", "tasks.user" => function ($query) {
                        $query->select('name', 'email', 'phone', '_id', "type");
                    }, "tasks" => function ($query) {
                        $query->select('schedule_id', 'user_id');
                    }])->where(function ($query) use ($request) {
                        if ($request->has('is_completed')) {
                            $query->where('is_completed', $request->is_completed);
                        }
                        if ($request->has('due_date')) {
                            $query->whereDate('due_date', $request->due_date);
                        }
                    })->get();

                foreach ($userWithReportNo as $schedule) {
                    $reportNo = optional($schedule->submission)->report_no;

                    foreach ($schedule->tasks as $task) {
                        $user = $task->user;
                        if ($user) {
                            $formatted[] = [
                                'report_no' => $reportNo,
                                'name'      => $user->name,
                                'email'     => $user->email,
                                'phone'     => $user->phone,
                                'type'      => $user->type,
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Handle the exception if needed
            }


            $uri = "/form/" . $request->id;

            [$result] = ApiService::makeRequest('GET', $uri);

            return response()->json(['data' => $collection, 'schema' => $result, "users" => $formatted], 200);
        }
        return response()->json(['data' => []], 400);
    }

    public static function search($request)
    {
        $columns = $request->columns;
        if ($request->id) {
            $collection = FormSubmission::where('form_id', $request->id)->orderBy('created_at', 'dsc');

            if (isset($reques->submissionId)) {
                $collection  =  $collection->where("_id", $request->submissionId);
            }

            if ($request->search || $request->isExact) {
                $searchTerm = trim($request->search);

                $collection->where(function ($query) use ($searchTerm, $columns, $request) {
                    foreach ($columns as $column) {
                        $column = "data." . $column;
                        if ($request->isExact) {
                            $query->orWhere($column . '.label', '=', $searchTerm)
                                ->orWhere($column, '=', $searchTerm);
                        } else {
                            $searchTermLike = '%' . $searchTerm . '%';
                            $query->orWhere($column . '.label', 'LIKE', $searchTermLike)
                                ->orWhere($column, 'LIKE', $searchTermLike);
                        }
                    }
                });
            }

            if ($request->has('limit')) {
                $collection->limit($request->limit);
            }

            if ($request->isFirst) {
                $data = $collection->first()->data ?? null; // Retrieve the first record's 'data' attribute
            } else {
                $data = $collection->pluck('data');
            }


            return response()->json(['data' => $data], 200);
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
        if (function_exists('beforeFormSubmissionCreate')) {
            $res =   beforeFormSubmissionCreate($request);
            if ($res) {
                return $res;
            }
        }

        $data  = [
            "form_id" => $request->id,
            "data" => $request->data,
            "user_id" => auth()->user()->_id ?? $request->user_id ?? "663cc2f6f98b750b9b071394",
            "schema_version" => $request->schema_version ? $request->schema_version : '',
            "report_no" => (string) self::generateReportNo(),
            "support_ids" => $request["support_ids"] ?? null
        ];

        if (auth()->user()) {
            if (auth()->user()->type != 'facility') {
                $data['status'] = PackageDeclarations::ALL_STATUS['APPROVED'];
            } else {
                // $site_id = isset($request->data['site']['value']) ? $request->data['site']['value'] : '';
                // $getSubmissions =  FormSubmission::where('form_id', $request->id)->where('user_id', auth()->user()->_id)->where('data.site.value', $site_id)->where('created_at', '>=', Carbon::now()->subMonths(3)->startOfDay())->get();
                // if (count($getSubmissions) > 0) {
                //     return response()->json(['data' => "You have already made a submission. You cannot submit again for the next three months."], 404);
                // }
            }
        }

        $submission = FormSubmission::create($data);

        if (!$submission) return response()->json(['data' => "Submisson not created"], 402);

        if (function_exists('afterFormSubmissionCreate')) {
            afterFormSubmissionCreate($submission);
        }
        return response()->json(['data' => 'Submission Successfully created', "submission" => $submission], 200);
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
        if (function_exists('beforeFormSubmissionUpdate')) {
            $res =   beforeFormSubmissionUpdate($request);
            if ($res) {
                return $res;
            }
        }

        if (FormSubmission::doesntExist('_id', $request->id)) return response()->json(['data' => []], 400);
        FormSubmission::where('_id', $request->id)->update([
            "data" => $request->data,
            "support_ids" => $request["support_ids"] ?? null
        ]);
        if (function_exists('afterFormSubmissionUpdate')) {
            afterFormSubmissionUpdate($request->id);
        }
        return response()->json(['data' => 'Submission Successfully Updated'], 200);
    }

    public static function destroy($id, $request)
    {
        $collection = FormSubmission::find($id);

        if (isset($request->parent_id)) {
            $parent = FormSubmission::find($request->parent_id);
            if ($parent) {
                $parent->support_ids = Arr::where($parent->support_ids, function ($value) use ($id) {
                    return $value["_id"] != $id;
                });
                $parent->save();
            }
        }
        if ($collection) $collection->delete();

        if (function_exists('afterFormSubmissionDelete')) {
            afterFormSubmissionDelete($id,  $request);
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
