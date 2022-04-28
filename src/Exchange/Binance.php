<?php

namespace Src\Exchange;

class Binance extends Exchange
{

    use Websocket\Binance, \Src\Cache\Binance;

    protected string $base_url = 'https://api.binance.com';

    protected string $base_url_futures = 'https://fapi.binance.com';

    public function fetchKlinesFutures(string $symbol, string $interval, int $limit = 1000, bool $removeCurrent = false): array
    {

        foreach ($this->getKlinesApiFutures($symbol, $interval, $limit) as $key => $candle)
            $candles[$key] = [
                'open' => $candle[1],
                'high' => $candle[2],
                'low' => $candle[3],
                'close' => $candle[4],
                'volume' => $candle[5],
                'time_start' => date('Y-m-d H:i:s', $candle[0] / 1000)
            ];

        $this->removeCurrentCandle($candles, $interval, $removeCurrent);

        return array_values($candles ?? []);

    }

    public function fetchKlines(string $symbol, string $interval, int $limit = 1000, bool $removeCurrent = false): array
    {

        foreach ($this->getKlinesApi($symbol, $interval, $limit) as $candle)
            $candles[] = [
                'open' => $candle[1],
                'high' => $candle[2],
                'low' => $candle[3],
                'close' => $candle[4],
                'volume' => $candle[5],
                'time_start' => date('Y-m-d H:i:s', $candle[0] / 1000)
            ];

        $this->removeCurrentCandle($candles, $interval, $removeCurrent);

        return array_values($candles ?? []);

    }

    private function getKlinesApi(string $symbol, string $interval, $limit = 1000): array
    {

        return $this->request(
            'get',
            $this->base_url . '/api/v3/klines',
            ['symbol' => $symbol, 'interval' => $interval, 'limit' => $limit],
        );

    }

    private function getKlinesApiFutures(string $symbol, string $interval, $limit = 1000): array
    {

        return $this->request(
            'get',
            $this->base_url_futures . '/fapi/v1/klines',
            ['symbol' => $symbol, 'interval' => $interval, 'limit' => $limit],
        );

    }

    private function removeCurrentCandle( array &$candles, string $interval, bool $removeCurrent)
    {


        if ($removeCurrent) {

            $current_candle = array_pop($candles);

            if ($current_candle['time_start'] < KlineInterval::maxCandleTimeStart($interval))
                $candles[] = $current_candle;

        }


    }

}