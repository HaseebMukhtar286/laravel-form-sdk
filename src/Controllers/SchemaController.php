<?php

// namespace YourVendor\YourPackage\Controllers;
namespace haseebmukhtar286\LaravelFormSdk\Controllers;

use App\Http\Controllers\Controller;
use haseebmukhtar286\LaravelFormSdk\Models\Form;
use Illuminate\Http\Request;
use haseebmukhtar286\LaravelFormSdk\Services\SchemaService;

class SchemaController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }
    public function listingBySecretKey(Request $request)
    {
        return SchemaService::listingBySecretKey($request);
    }

    public function createForm(Request $request)
    {
        return SchemaService::createForm($request);
    }

    public function showFormById(Request $request)
    {
        return SchemaService::showFormById($request);
    }

    public function updateFormById(Request $request)
    {
        return SchemaService::updateFormById($request);
    }

    public function deleteFormById($id)
    {
        return SchemaService::deleteFormById($id);
    }

    public function listingBySecretKeyAll(Request $request)
    {
        return SchemaService::listingBySecretKeyAll($request);
    }

    public function getBuilder(Request $request)
    {
        return SchemaService::getBuilder($request);
    }

    public function fillForm(Request $request)
    {
        return SchemaService::fillForm($request);
    }

    public function updateSubmissionForm(Request $request)
    {
        return SchemaService::updateSubmissionForm($request);
    }

    public function getAllSubmissionForm(Request $request)
    {
        return SchemaService::getAllSubmissionForm($request);
    }

    public function getSubmissionShow(Request $request)
    {
        return SchemaService::getSubmissionShow($request);
    }

    public function deleteSubmission(Request $request)
    {
        return SchemaService::deleteSubmission($request);
    }

    public function getEditBuilderUrl(Request $request)
    {
        return SchemaService::getEditBuilderUrl($request);
    }
}
