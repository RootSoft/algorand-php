<?php

namespace Rootsoft\Algorand\Utils;

class Buffer
{
    /**
     * Convert the binary string to an array, starting from position 0.
     * @param string $data
     * @return array
     */
    public static function toArray(string $data) : array
    {
        $buffer = unpack('C*', $data);

        return array_values($buffer);
    }

    /**
     * Pack data to a binary string.
     * @param array $buffer
     * @return string
     */
    public static function toBinaryString(array $buffer) : string
    {
        return pack('C*', ...$buffer);
    }

}
