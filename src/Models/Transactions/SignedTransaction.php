<?php

namespace Rootsoft\Algorand\Models\Transactions;

use MessagePack\Type\Bin;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Crypto\MultiSignature;
use Rootsoft\Algorand\Crypto\Signature;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Utils\Encoder;
use Rootsoft\Algorand\Utils\MessagePackable;

/**
 * Class SignedTransaction.
 */
class SignedTransaction implements MessagePackable
{
    /**
     * The internal transaction.
     * @var RawTransaction
     */
    private RawTransaction $transaction;

    /**
     * The auth address.
     * @var Address|null
     */
    private ?Address $authAddr = null;

    /**
     * The signature of the transaction.
     * @var Signature|null
     */
    private ?Signature $signature;

    /**
     * The logic signature.
     * @var LogicSignature|null
     */
    private ?LogicSignature $logicSignature;

    /**
     * The multi signature.
     * @var MultiSignature|null
     */
    private ?MultiSignature $multiSignature;

    /**
     * SignedTransaction constructor.
     * @param RawTransaction $transaction
     * @param Signature|null $signature
     * @param LogicSignature|null $logicSignature
     * @param MultiSignature|null $multiSignature
     */
    public function __construct(
        RawTransaction  $transaction,
        ?Signature      $signature = null,
        ?LogicSignature $logicSignature = null,
        ?MultiSignature $multiSignature = null
    )
    {
        $this->transaction = $transaction;
        $this->signature = $signature;
        $this->logicSignature = $logicSignature;
        $this->multiSignature = $multiSignature;
    }

    /**
     * Create a new signed transaction and sign it with the given signature.
     *
     * @param RawTransaction $transaction
     * @param Signature $signature
     * @return SignedTransaction
     */
    public static function fromSignature(RawTransaction $transaction, Signature $signature): self
    {
        return new self($transaction, $signature);
    }

    /**
     * Create a new signed transaction and sign it with the logic signature.
     *
     * @param RawTransaction $transaction
     * @param LogicSignature $logicSignature
     * @return SignedTransaction
     */
    public static function fromLogicSignature(RawTransaction $transaction, LogicSignature $logicSignature): self
    {
        return new self($transaction, null, $logicSignature);
    }

    /**
     * Create a new signed transaction and sign it with the multi signature.
     *
     * @param RawTransaction $transaction
     * @param MultiSignature $multiSignature
     * @return SignedTransaction
     */
    public static function fromMultiSignature(
        RawTransaction $transaction,
        MultiSignature $multiSignature
    ): self
    {
        return new self($transaction, null, null, $multiSignature);
    }

    /**
     * Export the (encoded) transaction.
     *
     * @param $fileName
     * @return int|false The function returns the number of bytes that were written to the file, or
     * false on failure.
     */
    public function export($fileName)
    {
        $data = Encoder::getInstance()->encodeMessagePack($this->toMessagePack());

        return file_put_contents($fileName, $data);
    }

    /**
     * @return Address|null
     */
    public function getAuthAddr(): ?Address
    {
        return $this->authAddr;
    }

    /**
     * Set the auth address.
     * @param Address|null $authAddr
     */
    public function setAuthAddr(?Address $authAddr)
    {
        $this->authAddr = $authAddr;
    }

    /**
     * @return RawTransaction
     */
    public function getTransaction(): RawTransaction
    {
        return $this->transaction;
    }

    /**
     * @return Signature|null
     */
    public function getSignature(): ?Signature
    {
        return $this->signature;
    }

    /**
     * @return LogicSignature|null
     */
    public function getLogicSignature(): ?LogicSignature
    {
        return $this->logicSignature;
    }

    /**
     * @return MultiSignature|null
     */
    public function getMultiSignature(): ?MultiSignature
    {
        return $this->multiSignature;
    }

    public function toMessagePack(): array
    {
        return [
            'sgnr' => $this->authAddr ? $this->authAddr->address : null,
            'sig' => $this->signature ? new Bin($this->signature->bytes()) : null,
            'txn' => $this->transaction->toMessagePack(),
            'lsig' => $this->logicSignature,
            'msig' => $this->multiSignature,
        ];
    }

    /**
     * Get the base64-encoded representation of the transaction.
     *
     * @return string
     */
    public function toBase64(): string
    {
        return Base64::encode(Encoder::getInstance()->encodeMessagePack($this->toMessagePack()));
    }

    /**
     * Get the bytes of this signed transaction.
     *
     * @return false|string
     */
    public function toBytes()
    {
        return Encoder::getInstance()->encodeMessagePack($this->toMessagePack());
    }
}
