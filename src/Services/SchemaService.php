<?php

namespace haseebmukhtar286\LaravelFormSdk\Services;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SchemaService
{
    public static function listingBySecretKey($req)
    {
        $params = [
            'page' => isset($req['page']) ? $req['page'] : 2,
            'per_page' => isset($req['per_page']) ? $req['per_page'] : 10,
            'form_status' => isset($req['form_status']) ? $req['form_status'] : true,
            'is_template' => isset($req['is_template']) ? $req['is_template'] : true,
            'is_scheduled' => isset($req['is_scheduled']) ? $req['is_scheduled'] : true,
            'search' => isset($req['search']) ? $req['search'] : '',
            'fromDate' => isset($req['fromDate']) ? $req['fromDate'] : '',
            'toDate' => isset($req['toDate']) ? $req['toDate'] : '',
        ];

        $queryString = http_build_query($params);

        // Build the final URI with the query string
        $uri = '/formListingData?' . $queryString;


        // Make the request to the API
        [$result] = ApiService::makeRequest('GET', $uri, $params);

        // Return the result
        return $result;
    }
    public static function createForm(Request $request)
    {
        $uri = '/form';
        $body =  [
            "name" => $request->name,
            "campaign_title" => $request["campaign_title"] ?? "",
            "icon" => $request->icon,
            "is_template" => $request["is_template"] ?? false,
            "is_campaign" => $request->is_campaign ?? false,
            "start_date" => $request["start_date"] ?? null,
            "end_date" => $request["end_date"] ?? null,
            "meta_data" => $request['meta_data'] ?? null,
        ];

        [$result] = ApiService::makeRequest('POST', $uri, $body);
        return $result;
    }
    public static function updateForm(Request $request)
    {
        $uri = '/form/update-form';
        $body = [
            "name" => $request->name,
            "campaign_title" => $request["campaign_title"] ?? "",
            "icon" => $request->icon,
            "is_template" => $request["is_template"] ?? false,
            "is_campaign" => $request->is_campaign ?? false,
            "start_date" => $request["start_date"] ?? null,
            "end_date" => $request["end_date"] ?? null,
            "meta_data" => $request['meta_data'] ?? null,
        ];

        [$result] = ApiService::makeRequest('POST', $uri, $body);
        return $result;
    }

    public static function showFormById(Request $request, $id)
    {
        // $id = $request->id;
        $uri = "/form/$id?isDev=" . $request->isDev;

        [$result] = ApiService::makeRequest('GET', $uri);
        return $result;
    }

    public static function updateFormById(Request $request)
    {
        $id = $request->id;
        $uri = "/form/$id";
        $body = ["rawForm" => $request->rawForm];

        [$result] = ApiService::makeRequest('PUT', $uri,  $body);
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
        [$result] = ApiService::makeRequest('POST', $uri);
        $builderLink = ApiService::$FrontendUrl;

        $resultArr = [
            'link' => $builderLink . "otp/" . $result["otp"] . "/" . $request->id,
            'localHost' => "http://localhost:3000/" . "otp/" . $result["otp"] . "/" . $request->id
        ];

        return response()->json($resultArr, 200);
        // return $result;
    }

    public static function changeStatus(Request $request)
    {
        $uri = '/form/change-status';
        $body = [
            "id" => $request->id,
            "status" => $request->status,
        ];

        [$result] = ApiService::makeRequest('POST', $uri, $body);
        return $result;
    }

    // public static function fillForm(Request $request)
    // {
    //     $uri = '/fill';
    //     $body = [
    //         "id" => $request->id,
    //         "data" => $request->data,
    //         "user_id" => auth()->user()->_id
    //     ];

    //     [$result] = ApiService::makeRequest('POST', $uri, ['body' => $body]);
    //     return $result;
    // }

    // public static function updateSubmissionForm(Request $request)
    // {
    //     $uri = '/submission/update';
    //     $body = [
    //         "id" => $request->id,
    //         "data" => $request->data,
    //     ];

    //     [$result] = ApiService::makeRequest('PUT', $uri, ['body' => $body]);
    //     return $result;
    // }

    // public static function getAllSubmissionForm(Request $request)
    // {
    //     $uri = '/submissions';
    //     $body = ["id" => $request->id];
    //     [$result] = ApiService::makeRequest('GET', $uri, ['body' => $body]);
    //     return $result;
    // }

    // public static function getSubmissionShow(Request $request)
    // {
    //     $uri = '/submissions/show';
    //     $body = ["id" => $request->id];
    //     [$result] = ApiService::makeRequest('GET', $uri, ['body' => $body]);
    //     return $result;
    // }

    // public static function deleteSubmission(Request $request)
    // {
    //     $uri = '/submissions/delete';
    //     $body = ["id" => $request->id];
    //     [$result] = ApiService::makeRequest('DELETE', $uri, ['body' => $body]);
    //     return $result;
    // }

    // public static function getEditBuilderUrl(Request $request)
    // {
    //     $uri = '/otp';
    //     [$result] = ApiService::makeRequest('POST', $uri);
    //     return $result;
    // }
}
