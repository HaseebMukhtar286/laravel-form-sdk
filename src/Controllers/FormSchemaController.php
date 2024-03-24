<?php

namespace haseebmukhtar286\LaravelFormSdk\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use haseebmukhtar286\LaravelFormSdk\Services\FormSchemaService;

class FormSchemaController extends Controller
{
    
    public function store(Request $request)
    {
        return FormSchemaService::create($request);
    }
}
