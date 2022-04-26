<?php

namespace Src\Indicator;

abstract class Indicator
{

    abstract public function get(array $sources): array;

    public function put(&$candles)
    {

        $candles = array_replace_recursive($candles, $this->get($candles));

    }

}
