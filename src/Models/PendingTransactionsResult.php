<?php


namespace Rootsoft\Algorand\Models;

/**
 * A potentially truncated list of transactions currently in the node's transaction pool.
 *
 * You can compute whether or not the list is truncated if the number of elements in the top-transactions
 * array is fewer than total-transactions.
 * Class PendingTransactionsResult
 * @package Rootsoft\Algorand\Models
 */
class PendingTransactionsResult
{
    /**
     * An array of signed transaction objects.
     * @var array
     * @required
     */
    public array $topTransactions;

    /**
     * Total number of transactions in the pool.
     * @var int
     * @required
     */
    public int $totalTransactions;
}
