<?php

namespace Src\Cache;

use Src\Cache;
use Src\Exchange\KlineInterval;

trait Binance
{

    private \Src\Exchange\Binance $binance;
    private string $cache_folder = 'binance';

    private function fetchKlinesCache(string $symbol, string $interval, int $limit): mixed
    {

        return Cache::remember(
            $symbol . $interval . $limit,
            fn () => $this->getSecondsForCache($interval),
            function () use ($symbol, $interval, $limit) {
                return $this->binance->fetchKlines(
                    $symbol,
                    $interval,
                    $limit,
                    removeCurrent: true
                );
            }
        );

    }

    private function getSecondsForCache($interval): int
    {

        $time_interval = KlineInterval::timeframeInSeconds($interval);

        $seconds = $time_interval - time() % $time_interval - 1;

        return ($seconds < 86400) ? $seconds : 86400;

    }

}