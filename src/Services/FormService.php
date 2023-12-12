<?php

namespace haseebmukhtar286\LaravelFormSdk\Services;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FormService
{
    public static function listingBySecretKey()
    {
        $uri = '/formListingData';
        [$result] = ApiService::makeRequest('GET', $uri);
        return $result;
    }

    public static function createForm(Request $request)
    {
        $uri = '/form';
        $body = json_encode([
            "name" => $request->name,
            "icon" => $request->icon,
        ]);

        [$result] = ApiService::makeRequest('POST', $uri, ['body' => $body]);
        return $result;
    }

    public static function showFormById(Request $request)
    {
        $id = $request->id;
        $uri = "/form/$id";

        [$result] = ApiService::makeRequest('GET', $uri);
        return $result;
    }

    public static function updateFormById(Request $request)
    {
        $id = $request->id;
        $uri = "/form/$id";
        $body = json_encode(["rawForm" => $request->rawForm]);

        [$result] = ApiService::makeRequest('PUT', $uri, ['body' => $body]);
        return $result;
    }

    public static function deleteFormById($id)
    {
        $uri = "/form/$id";
        [$result] = ApiService::makeRequest('DELETE', $uri);
        return $result;
    }

    public static function listingBySecretKeyAll()
    {
        $uri = '/formListingAll';
        [$result] = ApiService::makeRequest('GET', $uri);
        return $result;
    }

    public static function getBuilder(Request $request)
    {
        $uri = '/otp';
        $body = json_encode(["secret" => ApiService::initialize()]);
        [$result] = ApiService::makeRequest('POST', $uri, ['body' => $body]);
        return $result;
    }

    public static function fillForm(Request $request)
    {
        $uri = '/fill';
        $body = json_encode([
            "secret" => ApiService::initialize(),
            "id" => $request->id,
            "data" => $request->data,
            "user_id" => auth()->user()->_id
        ]);

        [$result] = ApiService::makeRequest('POST', $uri, ['body' => $body]);
        return $result;
    }

    public static function updateSubmissionForm(Request $request)
    {
        $uri = '/submission/update';
        $body = json_encode([
            "secret" => ApiService::initialize(),
            "id" => $request->id,
            "data" => $request->data,
        ]);

        [$result] = ApiService::makeRequest('PUT', $uri, ['body' => $body]);
        return $result;
    }

    public static function getAllSubmissionForm(Request $request)
    {
        $uri = '/submissions';
        $body = json_encode(["id" => $request->id]);
        [$result] = ApiService::makeRequest('GET', $uri, ['body' => $body]);
        return $result;
    }

    public static function getSubmissionShow(Request $request)
    {
        $uri = '/submissions/show';
        $body = json_encode(["id" => $request->id]);
        [$result] = ApiService::makeRequest('GET', $uri, ['body' => $body]);
        return $result;
    }

    public static function deleteSubmission(Request $request)
    {
        $uri = '/submissions/delete';
        $body = json_encode(["id" => $request->id]);
        [$result] = ApiService::makeRequest('DELETE', $uri, ['body' => $body]);
        return $result;
    }

    public static function getEditBuilderUrl(Request $request)
    {
        $uri = '/otp';
        $body = json_encode(["secret" => ApiService::initialize()]);
        [$result] = ApiService::makeRequest('POST', $uri, ['body' => $body]);
        return $result;
    }
}
