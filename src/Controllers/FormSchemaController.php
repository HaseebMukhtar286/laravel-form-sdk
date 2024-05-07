<?php

namespace haseebmukhtar286\LaravelFormSdk\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use haseebmukhtar286\LaravelFormSdk\Services\FormSchemaService;

class FormSchemaController extends Controller
{
    public function index(Request $request)
    {
        return FormSchemaService::index($request);
    }

    public function store(Request $request)
    {
        return FormSchemaService::create($request);
    }
}
