<?php

namespace Src\Indicator;

class Rma extends Indicator
{

    private int $length;
    private string $source;
    private string $name;

    public function __construct(int $length = 20, string $source = 'close', string $name = 'rma')
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

                $rma[] = [$this->name => null];

                continue;

            }

            if (!isset($first)) {

                $sum = 0;

                for ($i = 0; $i < $this->length; $i++)
                    $sum += $sources[$key - $i];

                $rma[] = [$this->name => $sum / $this->length];

                $first = false;

            } else
                $rma[] = [$this->name => ($rma[$key - 1][$this->name] * ($this->length - 1) + $source) / $this->length];

        }

        return $rma ?? [];

    }

}
