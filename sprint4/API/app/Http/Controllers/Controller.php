<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Spatie\ArrayToXml\ArrayToXml;
/**
 * @OA\Info(
 *   title="Toolshop API",
 *   version="4.0.0",
 *   description="Toolshop REST API technical description",
 *   @OA\Contact(
 *     email="info@testsmith.io",
 *     name="Testsmith"
 *   )
 * )
 * @OA\Server(
 *     description="Deployed environment",
 *     url="https://api-v4.practicesoftwaretesting.com"
 * ),
 * @OA\Server(
 *     description="Local environment",
 *     url="http://localhost:8091"
 * )
 */
class Controller extends BaseController
{

    private function makeXML($xml, $status = 200, array $headers = [], $xmlRoot = 'response', $encoding = null)
    {
        if (is_array($xml)) {
            $xml = ArrayToXml::convert($xml, $xmlRoot, true, $encoding);
        } elseif (is_object($xml) && method_exists($xml, 'toArray')) {
            $xml = ArrayToXml::convert($xml->toArray(), $xmlRoot, true, $encoding);
        } elseif (is_string($xml)) {
            $xml = $xml;
        } else {
            $xml = '';
        }
        if (!isset($headers['Content-Type'])) {
            $headers = array_merge($headers, ['Content-Type' => 'application/xml']);
        }
        return response($xml, $status)->withHeaders($headers);
    }

    protected function preferredFormat($data, $status = 200, array $headers = [], $xmlRoot = 'response'): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        if (strcmp(app('request')->headers->get('Accept'), 'xml') == 0) {
            return $this->makeXML($data, $status, array_merge($headers, ['Content-Type' => app('request')->headers->get('Accept')]), $xmlRoot);
        } else {
            return response()->json($data, $status,
                ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    protected function respondWithToken(string $token)
    {
        return $this->preferredFormat([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => app('auth')->factory()->getTTL() * 60
        ]);
    }

}
