<?php

namespace Rootsoft\Algorand\Models\Keys;

use Rootsoft\Algorand\Exceptions\AlgorandException;

class ParticipationPublicKey
{
    /**
     * The length of the public key.
     */
    public const PUBLIC_KEY_LENGTH = 32;

    private string $bytes;

    /**
     * ParticipationPublicKey constructor.
     * @param string $bytes
     * @throws AlgorandException
     */
    public function __construct(string $bytes)
    {
        if (strlen($bytes) != self::PUBLIC_KEY_LENGTH) {
            throw new AlgorandException('Participation Public Key wrong length.');
        }

        $this->bytes = $bytes;
    }

    /**
     * Get the bytes for this participation key.
     * @return string
     */
    public function getBytes(): string
    {
        return $this->bytes;
    }
}
