<?php

// namespace App\Http\Controllers\Api;
namespace haseebmukhtar286\LaravelFormSdk\Controllers;

use App\Http\Controllers\Controller;
use haseebmukhtar286\LaravelFormSdk\Models\FormSchema;
use haseebmukhtar286\LaravelFormSdk\Models\FormSubmission;
use haseebmukhtar286\LaravelFormSdk\Services\FormSchemaService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }
    public function submitionData(Request $request)
    {
        if ($request->api_key != env('API_KEY')) {
            return response()->json(["message" => "Unauthenticated."], 401);
        }

        if ($request->slug) {
            $formId = FormSchema::select('form_id')->where('data.slug', $request->slug)->first();
            if ($formId) {

                $id = '65f19406c304a7319c0f6a1c';
                $result = FormSubmission::where('form_id', $formId['form_id'])->get();

                return response()->json(['data' => $result]);
            }
        }
        return response()->json(['data' => ['No slug found!']]);
    }
}
