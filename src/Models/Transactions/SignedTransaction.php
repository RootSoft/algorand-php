<?php


namespace Rootsoft\Algorand\Models\Transactions;

use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Utils\Encoder;
use Rootsoft\Algorand\Utils\MessagePackable;

/**
 * Class SignedTransaction
 * @package Rootsoft\Algorand\Models\Transactions
 */
class SignedTransaction implements MessagePackable
{

    /**
     * The signature of the transaction.
     * @var string
     */
    private string $signature;

    /**
     * The internal transaction
     * @var RawTransaction
     */
    private RawTransaction $transaction;

    /**
     * The auth address.
     * @var Address|null
     */
    private ?Address $authAddr;

    /**
     * SignedTransaction constructor.
     * @param string $signature
     * @param RawTransaction $transaction
     */
    public function __construct(string $signature, RawTransaction $transaction)
    {
        $this->signature = $signature;
        $this->transaction = $transaction;
    }

    /**
     * Export the (encoded) transaction.
     *
     * @param $fileName
     */
    public function export($fileName)
    {
        $data = Encoder::getInstance()->encodeMessagePack($this->toMessagePack());
        file_put_contents($fileName, $data);
    }

    public function setAuthAddr(Address $authAddr)
    {
        $this->authAddr = $authAddr;
    }

    public function toMessagePack(): array
    {
        return [
            'sgnr' => $this->authAddr->address ?? null,
            'sig' => $this->signature,
            'txn' => $this->transaction->toMessagePack(),
        ];
    }
}
