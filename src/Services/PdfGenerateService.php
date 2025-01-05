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
            $formSubmission = FormSubmission::with("user")->find($id);

            if (!$formSubmission) {
                return response()->json(['message' => 'No Record Found'], 404);
            }

            $data = [];
            $data['formSubmission'] = $formSubmission;
            $uri = "/form/" . $formSubmission['form_id'];

            try {
                [$result] = ApiService::makeRequest('GET', $uri);
                $data['schema'] = $result;
            } catch (\Exception $e) {
                return response()->json(['message' => 'Failed to fetch schema', 'error' => $e->getMessage()], 500);
            }

            return response()->json(['data' => $data], 200);
        }

        return response()->json(['message' => 'Invalid ID provided'], 400);
    }
}
