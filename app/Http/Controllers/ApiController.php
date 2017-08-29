<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ApiController extends Controller
{
    /**
     * Handles GET requests to provider API
     *
     * @param $api
     * @param $params
     * @return mixed
     */
    public function providerGetApi($api, $params)
    {
        $params['format'] = 'json';
        $client = new Client();
        try {
            $response = $client->request('GET',env('R_API_HOST') . $api, [
                'headers' => [
                    'merchant_fid' => env('R_API_MERCHANT_FID'),
                    'api_key' => env('R_API_KEY'),
                    'version' => env('R_API_VERSION'),
                ],
                'query' => http_build_query($params)
            ]);
        } catch (GuzzleException $e) {
            $error = array(
                'api' => $api,
                'error' => json_decode($e->getResponse()->getBody()->getContents(), true)['response']['message']
            );
            ExceptionController::insertException('api', 'providerGetApi', $error);
            return response($error, 400)->throwResponse();
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * Handles PUT requests to provider API
     *
     * @param $api
     * @param $params
     * @return mixed
     */
    public function providerPutApi($api, $params)
    {
        $params['format'] = 'json';
        $client = new Client();
        try {
            $response = $client->request('PUT',env('R_API_HOST') . $api, [
                'headers' => [
                    'merchant_fid' => env('R_API_MERCHANT_FID'),
                    'api_key' => env('R_API_KEY'),
                    'version' => env('R_API_VERSION'),
                ],
                'form_params' => $params
            ]);
        } catch (GuzzleException $e) {
            $error = array(
                'api' => $api,
                'error' => json_decode($e->getResponse()->getBody()->getContents(), true)['response']['message']
            );
            ExceptionController::insertException('api', 'providerPutApi', $error);
            return response($error, 400)->throwResponse();
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * Handles POST requests to provider API
     *
     * @param $api
     * @param $params
     * @return mixed
     */
    public function providerPostApi($api, $params)
    {
        $params['format'] = 'json';
        $client = new Client();
        try {
            $response = $client->request('POST',env('R_API_HOST') . $api, [
                'headers' => [
                    'merchant_fid' => env('R_API_MERCHANT_FID'),
                    'api_key' => env('R_API_KEY'),
                    'version' => env('R_API_VERSION'),
                ],
                'form_params' => $params
            ]);
        } catch (GuzzleException $e) {
            $error = array(
                'api' => $api,
                'error' => json_decode($e->getResponse()->getBody()->getContents(), true)['response']['message']
            );
            ExceptionController::insertException('api', 'providerPostApi', $error);
            return response($error, 400)->throwResponse();
        }

        return json_decode($response->getBody(), true);
    }
}
