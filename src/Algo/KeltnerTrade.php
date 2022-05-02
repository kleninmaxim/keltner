<?php

namespace Src\Algo;

use Src\Exchange\Binance;
use Src\Indicator\KeltnerChannels;

class KeltnerTrade
{

    public function getKlines(array $candles): array
    {

        $candles = array_values($candles);

        (new KeltnerChannels(22, 3, 'ATR', 32))->put($candles);

        return array_values(
            array_reverse(
                array_filter(
                    $candles,
                    fn ($candle) => $candle['keltner_channel_upper'] && $candle['keltner_channel_lower']
                )
            )
        );

    }

    public function checkPositions(array $candles, array &$position): array
    {

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