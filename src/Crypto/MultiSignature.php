<?php

namespace Rootsoft\Algorand\Crypto;

use Rootsoft\Algorand\Utils\MessagePackable;

class MultiSignature implements MessagePackable
{
    const MULTISIG_VERSION = 1;

    private int $version;
    private int $threshold;
    private array $subsigs;

    /**
     * @param int $version
     * @param int $threshold
     * @param array $subsigs
     */
    public function __construct(int $version, int $threshold, array $subsigs)
    {
        $this->version = $version;
        $this->threshold = $threshold;
        $this->subsigs = $subsigs;
    }

    /**
     * Performs signature verification.
     *
     * @param string $data
     * @return bool
     */
    public function verify(string $data):bool
    {
        return false;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return int
     */
    public function getThreshold(): int
    {
        return $this->threshold;
    }

    /**
     * @return array
     */
    public function getSubsigs(): array
    {
        return $this->subsigs;
    }

    public function toMessagePack(): array
    {
        return [
            'v' => $this->version,
            'thr' => $this->threshold,
            'subsig' => $this->subsigs,
        ];
    }
}