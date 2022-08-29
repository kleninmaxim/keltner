<?php

namespace Src\Exchange\Bybit;

use Src\Exchange\Binance\Exchange;

class Bybit extends Exchange
{

    protected string $base_url_futures = 'https://api-testnet.bybit.com';

    protected string $public_api;

    protected string $private_api;

    public function __construct(string $public_api, string $private_api)
    {

        $this->public_api = $public_api;

        $this->private_api = $private_api;

    }

    public function getBalance(): array
    {

        return $this->sendPrivateRequest(
            'get',
            '/v2/private/wallet/balance',
            proofs: ['result']
        );

    }

    public function createOrder(string $symbol, string $order_type, string $side, float $qty, bool $reduce_only = true, bool $close_on_trigger = true, string $time_in_force = 'GoodTillCancel'): array
    {

        if (!in_array($order_type, ['Limit', 'Market']) || !in_array($side, ['Buy', 'Sell']))
            return [];

        return $this->sendPrivateRequest(
            'post',
            '/private/linear/order/create',
            [
                'symbol' => $symbol,
                'order_type' => $order_type,
                'side' => $side,
                'qty' => $qty,
                'time_in_force' => $time_in_force,
                'reduce_only' => $reduce_only,
                'close_on_trigger' => $close_on_trigger
            ],
            ['result']
        );

    }

    public function getMyPositions(string $symbol = null): array
    {

        return $this->sendPrivateRequest(
            'get',
            '/private/linear/position/list',
            $symbol ? ['symbol' => $symbol] : [],
            ['result']
        );

    }

    public function setLeverage(string $symbol, int $buy_leverage, int $sell_leverage): array
    {

        return $this->sendPrivateRequest(
            'post',
            '/private/linear/position/set-leverage',
            ['symbol' => $symbol, 'buy_leverage' => $buy_leverage, 'sell_leverage' => $sell_leverage],
            ['ret_code', 'ret_msg']
        );

    }

    private function sendPrivateRequest(string $method, string $url, array $get_params = [], array $proofs = []): array
    {

        $get_params['api_key'] = $this->public_api;

        $get_params['timestamp'] = $this->getTimestamp();

        ksort($get_params);

        return $this->request(
            $method,
            $this->base_url_futures . $url,
            array_merge(
                $get_params,
                ['sign' => $this->generateSignature($get_params)]
            ),
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