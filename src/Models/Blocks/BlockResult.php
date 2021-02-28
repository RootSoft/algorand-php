<?php


namespace Rootsoft\Algorand\Models\Blocks;

class BlockResult
{
    /**
     * The block header data
     * @var array
     * @required
     */
    public array $block;

    /**
     * Optional certificate object.
     * This is only included when the format is set to message pack.
     * @var array|null
     */
    public array $cert;
}
