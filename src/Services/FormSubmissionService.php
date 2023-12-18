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

class FormSubmissionService
{
    public static function paginate($request)
    {
        $per_page = $request->per_page ? $request->per_page : 20;
        $collection = FormSubmission::where('form_id', $request->id)->with('user')->orderBy('created_at', 'dsc')
            // ->with('region:name', 'site', 'user')
            // ->whereRelation('site', 'active', true)
        ;
        if ($request->search) {
            // $collection->where(function ($subQuery) use ($request) {
            //     $subQuery->whereRelation('region', 'name', 'LIKE', '%' . $request->search . '%')
            //         ->orWhereRelation('user', 'name', 'LIKE', '%' . $request->search . '%')
            //         ->orWhereRelation('site', 'name', 'LIKE', '%' . $request->search . '%');
            // });
        }
        if ($request->fromDate) {
            $fromDate = Carbon::parse($request->fromDate);
            $toDate = Carbon::parse($request->toDate);
            $collection = $collection->whereBetween('created_at', [$fromDate->startOfDay(), $toDate->endOfDay()]);
        }
        $collection = $collection->paginate(intVal($per_page));
        return response()->json(['data' => $collection], 200);
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
            "user_id" => auth()->user()->_id
        ];
        $submission = FormSubmission::create($data);
        if (!$submission) return response()->json(['data' => "Submisson not created"], 402);

        return response()->json(['data' => 'Submission Successfully created'], 200);
    }

    public static function update($request)
    {
        if (FormSubmission::doesntExist('_id', $request->id)) return response()->json(['data' => []], 400);
        FormSubmission::where('_id', $request->id)->update([
            "data" => $request->data
        ]);
        return response()->json(['data' => 'Submission Successfully Updated'], 200);
    }

    public static function destroy($id)
    {
        $collection = FormSubmission::find($id);

        if ($collection) $collection->delete();
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
}
