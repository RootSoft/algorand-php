<?php


namespace Rootsoft\Algorand\Models\Teals;

use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Models\Applications\TEALProgram;

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

    /// Get the base64 decoded program bytes.
    public function bytes(): string
    {
        return Base64::decode($this->result);
    }

    /// Get the compiled TEAL program.
    public function program(): TEALProgram
    {
        return new TEALProgram($this->bytes());
    }

}
