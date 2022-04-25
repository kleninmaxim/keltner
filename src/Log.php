<?php

namespace Src;

class Log
{

    public static function error(string $file, string $record): void
    {

        $directory = LOGS_PATH . 'error/';

        if (!file_exists($directory))
            mkdir($directory, 0755, true);

        file_put_contents(
            $directory . $file . '.log',
            date('Y-m-d H:i:s') . "\n" . $record . "\n\n",
            FILE_APPEND
        );

    }

}