<?php

namespace Src;

class Cache
{

    public static function remember(string $key, int|callable $seconds, callable $callback): mixed
    {

        if ($cache = self::get($key))
            return $cache;

        $data = $callback();

        if (self::set($data, $key, is_callable($seconds) ? $seconds() : $seconds))
            return $data;

        return false;

    }

    public static function set(mixed $data, string $key, int $seconds): bool
    {

        $content['data'] = $data;

        if ($seconds > 0)
            $content['end_time'] = time() + $seconds;

        if (
            file_put_contents(
                self::getFile($key),
                serialize($content)
            )
        ) return true;

        return false;

    }

    public static function get(string $key): mixed
    {

        $file = self::getFile($key);

        if (file_exists($file)) {

            $content = unserialize(file_get_contents($file));

            if (!isset($content['end_time']) || (time() <= $content['end_time']))
                return $content['data'];

            unlink($file);

        }

        return false;

    }

    public static function delete(string $key): bool
    {

        $file = self::getFile($key);

        if (file_exists($file)) {

            unlink($file);

            return true;

        }

        return false;

    }

    private static function getFile(string $key): string
    {

        return CACHE_PATH . md5($key);

    }

}