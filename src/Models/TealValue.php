<?php

namespace Rootsoft\Algorand\Models;

/**
 * Represents a TEAL value.
 * Class TealValue.
 */
class TealValue
{
    /**
     * bytes value.
     * @var string
     * @required
     */
    public string $bytes;

    /**
     * Value type.
     * @var int
     * @required
     */
    public int $type;

    /**
     * Uint value.
     * @var int
     * @required
     */
    public int $uint;
}
