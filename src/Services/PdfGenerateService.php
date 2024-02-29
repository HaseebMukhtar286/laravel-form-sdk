<?php

namespace haseebmukhtar286\LaravelFormSdk\Services;
use haseebmukhtar286\LaravelFormSdk\Services\ApiService;
// use App\Models\Setting;

class PdfGenerateService
{

    public static function pdfGenerate($id)
    {
        if ($id) {
            $uri = "/form/" . $id;
            [$result] = ApiService::makeRequest('GET', $uri);
            $data['schema'] = $result;
            // $data['settings'] = Setting::where('key', 'TRANSLATION_ARABIC')->first();
            return response()->json(['data' => $data], 200);
        } 
    }

}
