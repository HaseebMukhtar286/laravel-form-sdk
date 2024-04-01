<?php

namespace haseebmukhtar286\LaravelFormSdk\Services;

use haseebmukhtar286\LaravelFormSdk\Models\FormSubmission;
use haseebmukhtar286\LaravelFormSdk\Services\ApiService;

// use App\Models\Setting;

class PdfGenerateService
{

    public static function pdfGenerate($id)
    {
        if ($id) {
            $formSubmission = FormSubmission::with("user", 'site:name,name_ar,licenseNumber,cluster_name')->find($id);
            if ($formSubmission) {

                $data['formSubmission'] = $formSubmission;
                $uri = "/form/" . $formSubmission['form_id'];

                [$result] = ApiService::makeRequest('GET', $uri);
                $data['schema'] = $result;
                // $data['settings'] = Setting::where('key', 'TRANSLATION_ARABIC')->first();
                return response()->json(['data' => $data], 200);
            } else {
                return response()->json(['data' => 'No Record Found'], 400);
            }
        }
    }
}
