<?php

namespace Rootsoft\Algorand\Crypto;

use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Utils\CryptoUtils;
use Rootsoft\Algorand\Utils\MessagePackable;

class MultiSignature implements MessagePackable
{
    public const MULTISIG_VERSION = 1;

    private int $version;

    private int $threshold;

    /**
     * @var array|MultisigSubsig[]
     */
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
     * @throws \SodiumException
     */
    public function verify(string $data):bool
    {
        if ($this->version != self::MULTISIG_VERSION || $this->threshold <= 0 || empty($this->subsigs)) {
            return false;
        }

        if ($this->threshold > count($this->subsigs)) {
            return false;
        }

        $verifiedCount = 0;

        for ($i = 0; $i < count($this->subsigs); $i++) {
            $subsig = $this->subsigs[$i];
            $signature = $subsig->getSignature();
            if (is_null($signature)) {
                continue;
            }

            $pk = Address::fromPublicKey($subsig->getPublicKey()->bytes());
            $verified = CryptoUtils::verify($data, $signature->bytes(), $pk->address);
            if ($verified) {
                $verifiedCount += 1;
            }
        }

        return $verifiedCount >= $this->threshold;
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
     * Add a multisig sub signature.
     * @param MultisigSubsig $subsig
     */
    public function addSubsig(MultisigSubsig $subsig)
    {
        $this->subsigs[] = $subsig;
    }

    /**
     * Updates a multisig subsig at the given index.
     * @param int $index
     * @param MultisigSubsig $subsig
     */
    public function updateSubsig(int $index, MultisigSubsig $subsig)
    {
        $this->subsigs[$index] = $subsig;
    }

    /**
     * @return array|MultisigSubsig[]
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
