<?php

namespace haseebmukhtar286\LaravelFormSdk\Services;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiService
{
    private static $baseUrl;
    private static $secret;

    public static function initialize()
    {
        self::$baseUrl = env('BUILDER_URL');
        self::$secret = env('BUILDER_SECRET');
    }

    public static function makeRequest($method, $uri, $data = null)
    {
        self::initialize();

        $data['secret'] = self::$secret;
        $client = new \GuzzleHttp\Client([
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data,
        ]);

        $url = self::$baseUrl . $uri;

        try {
            $response = $client->request($method, $url);

            return [
                self::jsonFormat($response),
                $response
            ];
            // return response()->json(json_decode($response->getBody()->getContents(), true), $response->getStatusCode());
        } catch (RequestException $e) {

            throw  $e;
            // return response()->json(['error' => 'Request failed: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            throw  $e;
            // return response()->json(['error' => 'An unexpected error occurred'], 500);
        }
    }

    public static function jsonFormat($response)
    {

        // return response()->json(json_decode($response->getBody()->getContents(), true), $response->getStatusCode());
        return json_decode($response->getBody()->getContents(), true);
    }
}
