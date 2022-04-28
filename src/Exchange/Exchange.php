<?php

namespace Src\Exchange;

use Src\Http;
use Src\Log;

class Exchange
{

    //method to do a request and proof correct answer
    protected function request(string $method, string $url, array $query = [], array $header = [], array $proofs = []): array
    {

        $request = Http::$method($url, $query, $header);

        if (is_array($request))
            return $this->proofRequest($request, $proofs);

        Log::error(
            'error',
            print_r(
                [
                    'message' => 'variable $request is not array',
                    '$request' => $request,
                    '$url' => $url,
                    '$query' => $query,
                    '$header' => $header,
                ],
            true
            )
        );

        return [];

    }

    private function proofRequest(array $request, array $keys): array
    {

        if (count(array_intersect_key(array_flip($keys), $request)) === count($keys))
            return $request;

        return [];

    }

}