<?php

// namespace YourVendor\YourPackage\Controllers;
namespace haseebmukhtar286\LaravelFormSdk\Controllers;

use App\Http\Controllers\Controller;
use haseebmukhtar286\LaravelFormSdk\Models\Form;
use Illuminate\Http\Request;
use haseebmukhtar286\LaravelFormSdk\Services\FormService;

class FormController extends Controller
{
    public function listingBySecretKey(Request $request)
    {
        return FormService::listingBySecretKey($request);
    }

    public function createForm(Request $request)
    {
        return FormService::createForm($request);
    }

    public function showFormById(Request $request)
    {
        return FormService::showFormById($request);
    }

    public function updateFormById(Request $request)
    {
        return FormService::updateFormById($request);
    }

    public function deleteFormById($id)
    {
        return FormService::deleteFormById($id);
    }

    public function listingBySecretKeyAll(Request $request)
    {
        return FormService::listingBySecretKeyAll($request);
    }

    public function getBuilder(Request $request)
    {
        return FormService::getBuilder($request);
    }

    public function fillForm(Request $request)
    {
        return FormService::fillForm($request);
    }

    public function updateSubmissionForm(Request $request)
    {
        return FormService::updateSubmissionForm($request);
    }

    public function getAllSubmissionForm(Request $request)
    {
        return FormService::getAllSubmissionForm($request);
    }

    public function getSubmissionShow(Request $request)
    {
        return FormService::getSubmissionShow($request);
    }

    public function deleteSubmission(Request $request)
    {
        return FormService::deleteSubmission($request);
    }

    public function getEditBuilderUrl(Request $request)
    {
        return FormService::getEditBuilderUrl($request);
    }
}
