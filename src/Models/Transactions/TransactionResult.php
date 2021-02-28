<?php


namespace Rootsoft\Algorand\Models\Transactions;

/**
 * A single transaction result.
 * Class SearchTransactionsResult
 * @package Rootsoft\Algorand\Models\Transactions
 */
class TransactionResult
{

    /**
     * Round at which the results were computed.
     * @var int
     * @required
     */
    public int $currentRound = 0;

    /**
     * The transaction
     * @var Transaction
     * @required
     */
    public Transaction $transaction;
}
