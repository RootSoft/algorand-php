<?php

namespace Rootsoft\Algorand\Crypto;

use Rootsoft\Algorand\Utils\MessagePackable;

class MultisigSubsig implements MessagePackable
{
    private Ed25519PublicKey $publicKey;

    /**
     * @var Signature|null
     */
    private ?Signature $signature;

    /**
     * @param Ed25519PublicKey $publicKey
     * @param Signature|null $signature
     */
    public function __construct(Ed25519PublicKey $publicKey, ?Signature $signature = null)
    {
        $this->publicKey = $publicKey;
        $this->signature = $signature;
    }

    /**
     * @return Ed25519PublicKey
     */
    public function getPublicKey(): Ed25519PublicKey
    {
        return $this->publicKey;
    }

    /**
     * @param Signature|null $signature
     */
    public function setSignature(?Signature $signature): void
    {
        $this->signature = $signature;
    }

    /**
     * @return Signature|null
     */
    public function getSignature(): ?Signature
    {
        return $this->signature;
    }

    public function toMessagePack(): array
    {
        return [
            'pk' => $this->publicKey->bytes(),
            's' => isset($this->signature) ? $this->signature->bytes() : null,
        ];
    }
}
