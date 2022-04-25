<?php

namespace Src;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Http
{

    private static Client $client;

    // made get request
    public static function get(string $url, array $query = [], array $headers = [], bool $json = true)
    {

        return self::request('GET', $url, $query, $headers, $json);

    }

    // made post request
    public static function post(string $url, array $query = [], array $headers = [], bool $json = true)
    {

        return self::request('POST', $url, $query, $headers, $json);

    }

    // made common request and have common logic
    private static function request(string $method, string $url, array $query = [], array $headers = [], bool $json = true)
    {

        if (!isset(self::$client))
            self::$client = new Client(['timeout' => 10]);

        try {

            $response = self::$client->request(
                $method,
                $url,
                [
                    'headers' => $headers,
                    'query' => $query
                ]
            );

        } catch (GuzzleException $e) {

            Log::error('error', $e->getMessage());

            return false;

        }

        if ($json)
            return json_decode($response->getBody(), true);

        return $response->getBody()->getContents();

    }

}