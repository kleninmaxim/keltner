<?php

namespace Src\Algo;

use Src\Config;
use Src\Exchange\Binance\Binance;
use Src\Exchange\Bybit\Bybit;
use Src\Indicator\KeltnerChannels;

class KeltnerTrade
{

    public function bybitTrade(string $side, float $price, int $leverage = 10)
    {

        if (!isset($this->bybit)) {

            $bybit_config = Config::get('bybit')['my'];

            $this->bybit = new Bybit($bybit_config['api_public'], $bybit_config['api_private']);

        }

        if ($position = $this->bybit->getMyPositions('BTCUSDT')) {

            $long_size = $position['result'][0]['size'];

            if ($long_size != 0) {

                $cansel_order = $this->bybit->createOrder('BTCUSDT', 'Market', 'Sell', $long_size);

                echo '[' . date('Y-m-d H:i:s') . '] Close order. Side: ' . $long_size . PHP_EOL;

            }

            $short_size = $position['result'][1]['size'];

            if ($short_size != 0) {

                $cansel_order = $this->bybit->createOrder('BTCUSDT', 'Market', 'Buy', $short_size);

                echo '[' . date('Y-m-d H:i:s') . '] Close order. Side: ' . $short_size . PHP_EOL;

            }

            if ($position['result'][0]['leverage'] != $leverage || $position['result'][1]['leverage'] != $leverage) {

                $set_leverage = $this->bybit->setLeverage('BTCUSDT', $leverage, $leverage);

                echo '[' . date('Y-m-d H:i:s') . '] Set leverage: ' . $leverage . PHP_EOL;

            }

            if ($balance = $this->bybit->getBalance()) {

                $amount = round($balance['result']['USDT']['available_balance'] / $price, 3);

                if (
                    $new_position = $this->bybit->createOrder(
                        'BTCUSDT',
                        'Market',
                        $side,
                        $amount,
                        false,
                        false
                    )
                ) {

                    echo '[' . date('Y-m-d H:i:s') . '] New position: ' . $amount . PHP_EOL;

                }

            }

        }

    }

    public function getKlines(array $candles, bool $atr): array
    {

        $candles = array_values($candles);

        if ($atr) {

            (new KeltnerChannels(22, 3, 'ATR', 32))->put($candles);

        } else {

            (new KeltnerChannels(8, 2, 'R'))->put($candles);

        }

        return array_values(
            array_reverse(
                array_filter(
                    $candles,
                    fn ($candle) => $candle['keltner_channel_upper'] && $candle['keltner_channel_lower']
                )
            )
        );

    }

    public function checkPositions(array $candles): array
    {

        $position = ['act' => ''];

        foreach (array_reverse($candles) as $candle) {

            if ($position['act'] == 'wait') {

                if ($position['side'] == 'buy' && $position['price'] <= $candle['high']) {

//                    $history_positions[] = [
//                        'side' => $position['side'],
//                        'price' => $position['price'],
//                        'time_start' => $candle['time_start']
//                    ];

                    $position['act'] = 'in_position';

                } elseif ($position['side'] == 'sell' && $position['price'] >= $candle['low']) {

//                    $history_positions[] = [
//                        'side' => $position['side'],
//                        'price' => $position['price'],
//                        'time_start' => $candle['time_start']
//                    ];

                    $position['act'] = 'in_position';

                } else {

                    if ($candle['keltner_channel_upper'] <= $candle['close'] && $position['side'] != 'buy') {

                        $position = [
                            'act' => 'wait',
                            'side' => 'buy',
                            'price' => $candle['high']
                        ];

                    } elseif ($candle['keltner_channel_lower'] >= $candle['close'] && $position['side'] != 'sell') {

                        $position = [
                            'act' => 'wait',
                            'side' => 'sell',
                            'price' => $candle['low']
                        ];

                    }

                }

            } elseif ($position['act'] == 'in_position') {

                if ($position['side'] == 'buy' && $candle['keltner_channel_lower'] >= $candle['close']) {

                    $position = [
                        'act' => 'wait',
                        'side' => 'sell',
                        'price' => $candle['low']
                    ];

                } elseif ($position['side'] == 'sell' && $candle['keltner_channel_upper'] <= $candle['close']) {

                    $position = [
                        'act' => 'wait',
                        'side' => 'buy',
                        'price' => $candle['high']
                    ];

                }

            } else {

                if ($candle['keltner_channel_upper'] <= $candle['close']) {

                    $position = [
                        'act' => 'wait',
                        'side' => 'buy',
                        'price' => $candle['high']
                    ];

                } elseif ($candle['keltner_channel_lower'] >= $candle['close']) {

                    $position = [
                        'act' => 'wait',
                        'side' => 'sell',
                        'price' => $candle['low']
                    ];

                }

            }

        }

        return $position;

    }

    public function getAmount(Binance $binance, array $precisions, float $price): float
    {

        $btc_usdt_position = $binance->getPositionInformationFutures('BTCUSDT')[0];

        if ($btc_usdt_position['positionAmt'] != 0)
            return round($btc_usdt_position['positionAmt'] * 2, $precisions['amount_precision']);

        $balance = $binance->getBalancesFutures()['totalMarginBalance'];

        return round($balance / $price * 1.01, $precisions['amount_precision']);


    }

}