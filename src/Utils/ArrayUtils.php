<?php


namespace Rootsoft\Algorand\Utils;

class ArrayUtils
{
    /**
     * Find a value for a key in a given array, or return null if the value is not found.
     *
     * @param array $array
     * @param string $key
     * @return mixed|null
     */
    public static function findValueOrNull(array $array, string $key)
    {
        return $array[$key] ?? null;
    }
}
