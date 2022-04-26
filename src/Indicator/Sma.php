<?php

namespace Src\Indicator;

class Sma extends Indicator
{

    private int $length;
    private string $source;
    private string $name;

    public function __construct(int $length = 20, string $source = 'close', string $name = 'sma')
    {

        $this->length = $length;
        $this->source = $source;
        $this->name = $name;

    }

    public function get($sources): array
    {

        if ($this->source != null)
            $sources = array_column($sources, $this->source);

        foreach ($sources as $key => $source) {

            if ($key < $this->length - 1) {

                $sma[] = [$this->name => null];

                continue;

            }

            $sum = 0;

            for ($i = 0; $i < $this->length; $i++)
                $sum += $sources[$key - $i];

            $sma[] = [$this->name => $sum / $this->length];

        }

        return $sma ?? [];

    }

}
