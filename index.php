<?php

use Src\Config;

require __DIR__ . '/vendor/autoload.php';

const CONFIG_PATH = __DIR__ . '/config/';

const CACHE_PATH = __DIR__ . '/storage/cache/';

const LOGS_PATH = __DIR__ . '/storage/logs/';

Config::save();

