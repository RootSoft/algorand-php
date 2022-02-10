<?php

namespace Rootsoft\Algorand\Models\Transactions;

use Rootsoft\Algorand\Models\Applications\AccountStateDelta;
use Rootsoft\Algorand\Models\Applications\EvalDeltaKeyValue;

/**
 * Given a transaction id of a recently submitted transaction, it returns
 * information about it.
 * There are several cases when this might succeed:
 * - transaction committed (committed round > 0)
 * - transaction still in the pool (committed round = 0, pool error = "")
 * - transaction removed from pool due to error
 * (committed round = 0, pool error != "").
 *
 * Or the transaction may have happened sufficiently long ago that the node
 * no longer remembers it, and this will return an error.
 */
class PendingTransaction
{
    /**
     * The application index if the transaction was found and it created an application.
     * @var int|null
     */
    public ?int $applicationIndex = null;

    /**
     * The number of the asset's unit that were transferred to the close-to address.
     * @var int|null
     */
    public ?int $assetClosingAmount = null;

    /**
     * The asset index if the transaction was found and it created an asset.
     * @var int|null
     */
    public ?int $assetIndex = null;

    /**
     * Rewards in microalgos applied to the close remainder to account.
     * @var int|null
     */
    public ?int $closeRewards = null;

    /**
     * Closing amount for the transaction.
     * @var int|null
     */
    public ?int $closingAmount = null;

    /**
     * The round where this transaction was confirmed, if present.
     * @var int|null
     */
    public ?int $confirmedRound = null;

    /**
     * Global state key/value changes for the application being executed by this transaction.
     * @var array|EvalDeltaKeyValue
     */
    public array $globalStateDelta = [];

    /**
     * Inner transactions produced by application execution.
     * @var array|PendingTransaction[]
     */
    public array $innerTxns = [];

    /**
     * Local state key/value changes for the application being executed by this transaction.
     * @var array|AccountStateDelta[]
     */
    public array $localStateDelta = [];

    /**
     * Logs for the application being executed by this transaction.
     * @var array|string[]
     */
    public array $logs = [];

    /**
     * Indicates that the transaction was kicked out of this node's transaction pool  (and specifies why that happened).
     *
     * An empty string indicates the transaction wasn't kicked out of this node's txpool due to an error.
     * @var string
     */
    public string $poolError;

    /**
     * Rewards in microalgos applied to the receiver account.
     *
     * @var int|null
     */
    public ?int $receiverRewards = null;

    /**
     * Rewards in microalgos applied to the sender account.
     *
     * @var int|null
     */
    public ?int $senderRewards = null;

    // key: txn
    //final SignedTransaction $transaction;
}
