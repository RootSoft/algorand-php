<?php

namespace Rootsoft\Algorand\Models\Applications;

use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Crypto\Signature;
use Rootsoft\Algorand\Models\Accounts\Account;

class TEALProgram
{
    /**
     * Prefix for signing TEAL program data
     */
    const PROGDATA_SIGN_PREFIX = 'ProgData';

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

    /**
     * Creates Signature compatible with ed25519verify TEAL opcode from data and program bytes.
     *
     * @param Account $account
     * @param string $data
     * @return Signature
     * @throws \Rootsoft\Algorand\Exceptions\AlgorandException
     * @throws \SodiumException
     */
    public function sign(Account $account, string $data) : Signature
    {
        $lsig = new LogicSignature($this->program);
        return $lsig->toAddress()->sign($account, $data);
    }
}
