<?php

namespace haseebmukhtar286\LaravelFormSdk\Services;

use App\Declarations\Declarations;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use haseebmukhtar286\LaravelFormSdk\Models\FormSchema;
use haseebmukhtar286\LaravelFormSdk\Services\SchemaService;
use haseebmukhtar286\LaravelFormSdk\Models\FormSubmission;
use Maatwebsite\Excel\Facades\Excel;
use haseebmukhtar286\LaravelFormSdk\Exports\SubmitionExport;

class ExcelGenerateService
{

    public static function excelgenerate($id)
    {
        if ($id) {
            $schemaData = SchemaService::showFormById($id);
            $dbData =  FormSubmission::where('form_id', $id)->get();
            if ($dbData && $schemaData) {
                $dataArray = self::dataPrepare($schemaData['data']['schema']);
                $dataArr = [];
                $finalArr = [];
                foreach ($dbData as $key => $value) {
                    foreach ($value['data'] as $key1 => $value1) {
                        if (array_key_exists($key1, $dataArray)) {
                            $data = trim(str_replace(array("\r", "\n", '"', ',', ';', '<', '>'), ' ', $dataArray[$key1]));
                            $colName = $data === "" ? $key1 : $data;
                            $dataArr[$colName] = $value1;
                        }
                    }
                    array_push($finalArr, $dataArr);
                }
                return Excel::download(new SubmitionExport($finalArr), 'data.xlsx');
            }
        }
    }

    public static function dataPrepare($structure)
    {
        $idToComponentMap = array();
        $stack = array(array('key' => '', 'value' => $structure));

        while (!empty($stack)) {
            $item = array_pop($stack);
            $key = $item['key'];
            $current = $item['value'];

            if (is_array($current)) {
                if (isset($current["skip-in-excel"]) && $current["skip-in-excel"] == true) {

                    continue;
                }
                $id = $key;
                $component = $current["title"] ?? '';
                $idToComponentMap[$id] = $component;
            }

            foreach ($current as $subKey => $subValue) {
                if (is_array($subValue)) {
                    array_push($stack, array('key' => $subKey, 'value' => $subValue));
                }
            }
        }
        return $idToComponentMap;
    }
}
