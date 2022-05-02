<?php

use Src\Algo\KeltnerTrade;
use Src\Config;
use Src\Exchange\Binance;
use Src\Exchange\KlineInterval;

require_once __DIR__ . '/index.php';

$account = 'my';

$asset = 'BTCUSDT';

$precisions = ['amount_precision' => 3, 'price_precision' => 2];

$binance_config = Config::get('binance');

$binance = new Binance($binance_config[$account]['api_public'], $binance_config[$account]['api_private']);

$binance->connectKlineStreamFutures($asset, KlineInterval::HOUR);

$keltner = new KeltnerTrade();

$position = ['act' => ''];

while (true) {

    $candles = $keltner->getKlines($binance->fetchKlinesFuturesCache($asset, KlineInterval::HOUR));

    $current_candle = $binance->receive();

    $current_candle = [
        'close' => $current_candle['k']['c'],
        'low' => $current_candle['k']['l'],
        'high' => $current_candle['k']['h']
    ];

    $keltner->checkPositions($candles, $position);

    if ($position['act'] == 'wait') {

        if ($position['side'] == 'buy' && $current_candle['close'] >= $position['price']) {

            $order = $binance->createOrderFutures(
                'BTCUSDT',
                'BUY',
                'MARKET',
                $keltner->getAmount($binance, $precisions, $position['price'])
            );

            $position = [
                'act' => 'in_position',
                'side' => $position['side'],
                'price' => $position['price']
            ];

        } elseif ($position['side'] == 'sell' && $current_candle['close'] <= $position['price']) {

            $order = $binance->createOrderFutures(
                'BTCUSDT',
                'SELL',
                'MARKET',
                $keltner->getAmount($binance, $precisions, $position['price'])
            );

            $position = [
                'act' => 'in_position',
                'side' => $position['side'],
                'price' => $position['price']
            ];

        }

    }

}
