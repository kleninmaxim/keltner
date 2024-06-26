<?php

namespace Src\Indicator;

class Atr extends Indicator
{

    private int $atr_period;
    private string $name;

    public function __construct(int $atr_period = 14, string $name = 'atr')
    {

        $this->atr_period = $atr_period;
        $this->name = $name;

    }

    public function get($sources): array
    {

        $trueRanges = [];

        foreach ($sources as $key => $source) {

            $trueRanges[$key] = !isset($sources[$key - 1]['high'])
                ? $source['high'] - $source['low']
                : max(
                    $source['high'] - $source['low'],
                    abs($source['high'] - $sources[$key - 1]['close']),
                    abs($source['low'] - $sources[$key - 1]['close'])
                );

        }

        foreach ((new Rma($this->atr_period, ''))->get($trueRanges) as $key => $rma)
            $atrs[$key][$this->name] = $rma['rma'];

        return $atrs ?? [];

    }

}
