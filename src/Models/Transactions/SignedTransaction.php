<?php


namespace Rootsoft\Algorand\Models\Transactions;

use MessagePack\MessagePack;
use MessagePack\Packer;
use MessagePack\TypeTransformer\BinTransformer;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Utils\AlgorandUtils;
use Rootsoft\Algorand\Utils\Encoder;

/**
 * Class SignedTransaction
 * @package Rootsoft\Algorand\Models\Transactions
 */
class SignedTransaction
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
        $data = Encoder::getInstance()->encodeMessagePack($this->toArray());
        file_put_contents($fileName, $data);
    }

    public function setAuthAddr(Address $authAddr)
    {
        $this->authAddr = $authAddr;
    }

    public function toArray()
    {
        return AlgorandUtils::algorand_array_clean([
            'sgnr' => $this->authAddr->address ?? null,
            'sig' => $this->signature,
            'txn' => $this->transaction->toArray(),
        ]);
    }
}
