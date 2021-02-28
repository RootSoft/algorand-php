<?php


namespace Rootsoft\Algorand\Models\Teals;

class TealCompilationResult
{

    /**
     * base32 SHA512_256 of program bytes (Address style)
     * @var string
     * @required
     */
    public string $hash;

    /**
     * base64 encoded program bytes
     * @var string
     * @required
     */
    public string $result;
}
