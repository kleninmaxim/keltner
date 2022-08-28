<?php

namespace Src\Cache;

use Src\Cache;
use Src\Exchange\Binance\KlineInterval;

trait Binance
{

    public function fetchKlinesFuturesCache(string $symbol, string $interval, int $limit = 1000): mixed
    {

        return Cache::remember(
            $symbol . $interval . $limit . '_futures',
            fn () => $this->getSecondsForCache($interval),
            function () use ($symbol, $interval, $limit) {
                return $this->fetchKlinesFutures(
                    $symbol,
                    $interval,
                    $limit,
                    removeCurrent: true
                );
            }
        );

    }

    public function fetchKlinesCache(string $symbol, string $interval, int $limit): mixed
    {

        return Cache::remember(
            $symbol . $interval . $limit,
            fn () => $this->getSecondsForCache($interval),
            function () use ($symbol, $interval, $limit) {
                return $this->fetchKlines(
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

        $seconds = $time_interval - time() % $time_interval;

        return ($seconds < 86400) ? $seconds : 86400;

    }

}