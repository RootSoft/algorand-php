<?php

namespace Rootsoft\Algorand\Models\Applications;

class TEALProgram
{
    private string $program;

    /**
     * @param string $program
     */
    public function __construct(string $program)
    {
        $this->program = $program;
    }

    /**
     * Create a new, TEAL program from the given source code.
     *
     * @param string $source
     * @return TEALProgram
     */
    public static function fromSourceCode(string $source): TEALProgram
    {
        return new self(utf8_encode($source));
    }

    /**
     * Get the program bytes.
     *
     * @return string
     */
    public function bytes(): string
    {
        return $this->program;
    }

    /// Creates Signature compatible with ed25519verify TEAL opcode from data and program bytes.
    public function sign()
    {
        // TODO Implementation
    }
}
