<?php

namespace haseebmukhtar286\LaravelFormSdk\Services;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiService
{
    public static $baseUrl;
    public static $FrontendUrl;
    private static $secret;

    public static function initialize()
    {
        self::$baseUrl = env('BUILDER_URL');
        self::$FrontendUrl = env('BUILDER_FRONTEND_URL');
        self::$secret = env('BUILDER_SECRET');
    }

    public static function makeRequest($method, $uri, $data = [], $queryParams = [])
    {
        self::initialize();

        // Parse URI to handle existing query params
        $parsedUri = parse_url($uri);
        $path = $parsedUri['path'] ?? $uri;
        $existingQuery = [];
        if (isset($parsedUri['query'])) {
            parse_str($parsedUri['query'], $existingQuery);
        }

        $url = self::$baseUrl . $path;

        $options = [
            'headers' => ['Content-Type' => 'application/json'],
        ];

        if (strtoupper($method) === 'GET') {
            // Merge existing query params, $data, and $queryParams for GET requests
            $allQueryParams = array_merge($existingQuery, $data, $queryParams);
            $allQueryParams['secret'] = self::$secret;
            $url = $url . '?' . http_build_query($allQueryParams);
        } else {
            $data['secret'] = self::$secret;
            $options['json'] = $data;
        }

        $client = new \GuzzleHttp\Client($options);

        try {
            $response = $client->request($method, $url);

            return [
                self::jsonFormat($response),
                $response
            ];
        } catch (RequestException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function jsonFormat($response)
    {

        // return response()->json(json_decode($response->getBody()->getContents(), true), $response->getStatusCode());
        return json_decode($response->getBody()->getContents(), true);
    }
}
