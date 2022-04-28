<?php

namespace Src\Exchange;

class Binance extends Exchange
{

    use Websocket\Binance, \Src\Cache\Binance;

    protected string $base_url = 'https://api.binance.com';

    protected string $base_url_futures = 'https://fapi.binance.com';

    protected string $public_api;

    protected string $private_api;

    public function __construct(string $public_api = '', string $private_api = '')
    {

        $this->public_api = $public_api;

        $this->private_api = $private_api;

    }

    public function getBalancesFutures(): array
    {

        return $this->sendPrivateRequest(
            'get',
            '/fapi/v1/account',
            proofs: ['assets', 'updateTime', 'availableBalance']
        );

    }

    public function createOrderFutures(
        string $symbol, //BTCUSDT
        string $type, //LIMIT MARKET STOP_LOSS TAKE_PROFIT
        string $side, //BUY SELL
        float $quantity = null,
        float $price = null,
        array $options = []
    ): array
    {

        $get_params = [
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side
        ];

        if ($quantity)
            $get_params['quantity'] = $quantity;

        if ($price)
            $get_params['price'] = $price;

        if (in_array($type, ['LIMIT', 'STOP', 'TAKE_PROFIT']))
            $get_params['timeInForce'] = 'GTC';

        if (isset($options['stopPrice']))
            $get_params['stopPrice'] = $options['stopPrice'];

        if (isset($options['closePosition']))
            $get_params['closePosition'] = $options['closePosition'];

        if (isset($options['reduceOnly']))
            $get_params['reduceOnly'] = $options['reduceOnly'];

        if (isset($options['workingType']))
            $get_params['workingType'] = $options['workingType'];

        return $this->sendPrivateRequest(
            'post',
            '/fapi/v1/order',
            $get_params,
            ['orderId', 'symbol']
        );

    }

    public function getPositionInformationFutures(string $symbol = ''): array
    {

        if ($symbol)
            $get_params['symbol'] = $symbol;

        return $this->sendPrivateRequest(
            'get',
            '/fapi/v2/positionRisk',
            $get_params ?? [],
            proofs: ['symbol', 'notional']
        );

    }

    public function changeInitialLeverageFutures(string $symbol, int $leverage): array
    {

        $get_params = [
            'symbol' => $symbol,
            'leverage' => $leverage
        ];

        return $this->sendPrivateRequest(
            'post',
            '/fapi/v1/leverage',
            $get_params,
            proofs: ['leverage', 'symbol']
        );

    }

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

    private function sendPrivateRequest(string $method, string $url, array $get_params = [], array $proofs = []): array
    {

        $get_params['timestamp'] = $this->getTimestamp();

        return $this->request(
            $method,
            $url,
            array_merge(
                $get_params,
                ['signature' => $this->generateSignature($get_params)]
            ),
            [
                'X-MBX-APIKEY' => $this->public_api,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            $proofs
        );

    }

    private function generateSignature($query): string
    {

        return hash_hmac('sha256', http_build_query($query), $this->private_api);

    }

    private function getTimestamp(): string
    {

        list($msec, $sec) = explode(' ', microtime());

        return $sec . substr($msec, 2, 3);

    }

}