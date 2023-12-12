<?php

// namespace App\Http\Controllers\Api;
namespace haseebmukhtar286\LaravelFormSdk\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use haseebmukhtar286\LaravelFormSdk\Services\FormSubmissionService;

class FormSubmissionController extends Controller
{
    public function index(Request $request)
    {
        return FormSubmissionService::paginate($request);
    }

    public function show($id)
    {
        return FormSubmissionService::find($id);
    }
    public function dashboard(Request $request)
    {
        return FormSubmissionService::dashboard($request);
    }
    public function store(Request $request)
    {
        return FormSubmissionService::create($request);
    }
    public function update(Request $request)
    {
        return FormSubmissionService::update($request);
    }
    public function destroy($id)
    {
        return FormSubmissionService::destroy($id);
    }
}
