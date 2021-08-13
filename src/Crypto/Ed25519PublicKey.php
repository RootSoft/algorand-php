<?php

namespace Rootsoft\Algorand\Crypto;

use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Utils\MessagePackable;

class Ed25519PublicKey implements MessagePackable
{
    const ED25519_KEY_SIZE = 32;

    private string $bytes;

    /**
     * Create a new Ed25519 Public Key.
     *
     * @param string $bytes
     * @throws AlgorandException
     */
    public function __construct(string $bytes)
    {
        if (strlen($bytes) != self::ED25519_KEY_SIZE) {
            throw new AlgorandException('ed25519 public key wrong length');
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
