<?php

namespace Rootsoft\Algorand\Models\Applications;

use Rootsoft\Algorand\Utils\MessagePackable;

/**
 * Specifies maximums on the number of each type that may be stored.
 *
 * Class StateSchema
 */
class StateSchema implements MessagePackable
{
    /**
     * The number of unsigned integers.
     * TODO bigint.
     *
     * @var int
     * @required
     */
    public int $numUint;

    /**
     * The number of byte slices.
     * TODO bigint.
     *
     * @var int
     * @required
     */
    public int $numByteSlice;

    /**
     * @param int $numUint
     * @param int $numByteSlice
     */
    public function __construct(int $numUint = 0, int $numByteSlice = 0)
    {
        $this->numUint = $numUint;
        $this->numByteSlice = $numByteSlice;
    }

    public function toMessagePack(): array
    {
        return [
            'nui' => $this->numUint,
            'nbs' => $this->numByteSlice,
        ];
    }
}
