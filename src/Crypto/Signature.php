<?php

namespace Rootsoft\Algorand\Crypto;

use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Utils\MessagePackable;

class Signature implements MessagePackable
{

    const ED25519_SIG_SIZE = 64;

    private string $bytes;

    /**
     * @param string $bytes
     */
    public function __construct(string $bytes)
    {
        if (strlen($bytes) != self::ED25519_SIG_SIZE) {
            throw new AlgorandException('Given signature length is not valid');
        }

        $this->bytes = $bytes;
    }

    /**
     * @return string
     */
    public function bytes(): string
    {
        return $this->bytes;
    }

    public function toMessagePack(): array
    {
        return [
            'bytes' => $this->bytes,
        ];
    }
}
