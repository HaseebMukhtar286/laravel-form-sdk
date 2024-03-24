<?php

namespace haseebmukhtar286\LaravelFormSdk\Services;

use haseebmukhtar286\LaravelFormSdk\Models\FormSchema;

class FormSchemaService
{
    public static function create($request)
    {
        $data  = [
            "form_id" => $request->schema_id,
            "data" => $request->data,
            "schema_version" => $request->version,
        ];
        $res = FormSchema::create($data);
        if (!$res) return response()->json(['data' => "Submisson not created"], 402);

        return response()->json(['data' => 'true'], 200);
    }

}
