<?php


namespace Rootsoft\Algorand\Models\Transactions;

use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Utils\AlgorandUtils;
use Rootsoft\Algorand\Utils\Encoder;

/**
 * An Atomic Transfer means that transactions that are part of the transfer either all succeed or all fail.
 * Atomic transfers allow complete strangers to trade assets without the need for a trusted intermediary,
 * all while guaranteeing that each party will receive what they agreed to.
 *
 * On Algorand, atomic transfers are implemented as irreducible batch operations, where a group of transactions are
 * submitted as a unit and all transactions in the batch either pass or fail.
 *
 * This also eliminates the need for more complex solutions like hashed timelock contracts that are implemented on
 * other blockchains. An atomic transfer on Algorand is confirmed in less than 5 seconds, just like any other transaction.
 *
 * Transactions can contain Algos or Algorand Standard Assets and may also be governed by Algorand Smart Contracts.
 *
 * Class AtomicTransfer
 * @package Rootsoft\Algorand\Models\Transactions
 */
class AtomicTransfer
{
    /**
     * The prefix for a transaction group.
     */
    const TG_PREFIX = 'TG';

    /**
     * The maximum allowed number of transactions in an atomic transfer.
     */
    const MAX_TRANSACTION_GROUP_SIZE = 16;

    /**
     * The transactions
     * @var RawTransaction[]
     */
    private array $transactions;

    /**
     * Group a list of transactions and assign them with a group id.
     *
     * @param RawTransaction[] $transactions
     * @param Address|null $address optional sender address specifying which transaction return
     * @return array
     * @throws AlgorandException
     */
    public static function group(array $transactions, ?Address $address = null)
    {
        // Calculate the group id and assign to each transaction
        $groupId = self::computeGroupId($transactions);
        $groupedTransaction = [];
        foreach ($transactions as $transaction) {
            if ($address == null || $address == $transaction->sender) {
                $transaction->assignGroupID($groupId);
                $groupedTransaction[] = $transaction;
            }
        }

        return $groupedTransaction;
    }

    /**
     * Compute the group id.
     *
     * @param RawTransaction[] $transactions
     * @return string
     * @throws AlgorandException
     */
    public static function computeGroupId(array $transactions)
    {
        if (count($transactions) == 0) {
            throw new AlgorandException('Empty transaction list');
        }

        if (count($transactions) > self::MAX_TRANSACTION_GROUP_SIZE) {
            throw new AlgorandException("Max. group size is " . self::MAX_TRANSACTION_GROUP_SIZE);
        }

        // Calculate the transaction ids for every transaction
        $transactionIds = [];
        foreach ($transactions as $transaction) {
            $transactionIds[] = $transaction->getRawTransactionId();
        }

        // Encode transaction as msgpack
        $encodedTx = Encoder::getInstance()->encodeMessagePack(
            [
                'txlist' => $transactionIds,
            ]
        );

        // Prepend the transaction group prefix
        $txBytes = implode(unpack("H*", self::TG_PREFIX));
        $encodedTx = hex2bin($txBytes) . $encodedTx;

        return AlgorandUtils::hash($encodedTx);
    }

    /**
     * Get the grouped transactions in this atomic transfer.
     *
     * @return RawTransaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }
}
