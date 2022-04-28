<?php

namespace Src\Exchange\Websocket;

use Src\Log;
use Throwable;
use WebSocket\Client;

trait Binance
{

    private Client $client;
    private string $base_websocket_url = 'wss://stream.binance.com:9443/ws/';
    private string $base_websocket_url_futures = 'wss://fstream.binance.com/ws/';

    public function connectKlineStreamFutures(string $symbol, string $interval): void
    {

        $this->client = new Client(
            $this->base_websocket_url_futures . mb_strtolower($symbol) . '@kline_' . $interval,
            ['timeout' => 100]
        );

    }

    public function connectKlineStream(string $symbol, string $interval): void
    {

        $this->client = new Client(
            $this->base_websocket_url . mb_strtolower($symbol) . '@kline_' . $interval,
            ['timeout' => 100]
        );

    }

    public function receive(): array
    {

        try {

            if ($receive = json_decode($this->client->receive(), true))
                return $receive;

        } catch (Throwable $e) {

            Log::error('error', $e->getMessage());

        }

        return [];

    }

    public function close(): void
    {

        $this->client->close();

    }

}