<?php

namespace Src\Exchange\Binance;

class KlineInterval
{

    const MIN = '1m';
    const MIN5 = '5m';
    const MIN15 = '15m';
    const HOUR = '1h';
    const HOUR4 = '4h';
    const DAY = '1d';
    const WEEK = '1w';
    const MONTH = '1M';

    public static function maxCandleTimeStart($timeframe): string
    {

        return date(
            'Y-m-d H:i:s',
            strtotime(date('Y-m-d H:i:s')) - self::timeframeInSeconds($timeframe)
        );

    }

    public static function timeframeInSeconds($timeframe): int
    {

        $timeframes = [
            self::MIN => 60,
            self::MIN5 => 300,
            self::MIN15 => 900,
            self::HOUR => 3600,
            self::HOUR4 => 14400,
            self::DAY => 86400,
            self::WEEK => 604800,
            self::MONTH => 2592000
        ];

        return $timeframes[$timeframe] ?? 0;

    }

}