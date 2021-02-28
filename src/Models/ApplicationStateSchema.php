<?php


namespace Rootsoft\Algorand\Models;

/**
 * Specifies maximums on the number of each type that may be stored.
 * Class ApplicationStateSchema
 * @package Rootsoft\Algorand\Models
 */
class ApplicationStateSchema
{
    /**
     * The number of byte slices.
     * @var int
     * @required
     */
    public int $numByteSlice;

    /**
     * The number of unsigned integers.
     * @var int
     * @required
     */
    public int $numUint;
}
