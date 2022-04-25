<?php

namespace Src;

class Config
{

    private static array $configs = [];

    public static function save(): void
    {

        foreach(scandir(CONFIG_PATH) as $config_file)
            if (str_contains($config_file, '.config.php'))
                self::$configs[str_replace('.config.php', '', $config_file)]
                    = require_once CONFIG_PATH . $config_file;

    }

    public static function get($file): mixed
    {

        return self::$configs[$file] ?? [];

    }

}