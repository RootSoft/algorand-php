<?php

namespace Rootsoft\Algorand\Models\Transactions;

/**
 * Class SearchTransactionsResult.
 */
class SearchTransactionsResult
{
    /**
     * Round at which the results were computed.
     * @var int
     * @required
     */
    public int $currentRound = 0;

    /**
     * Used for pagination, when making another request provide this token with the next parameter.
     * @var string|null
     */
    public ?string $nextToken;

    /**
     * A list of transactions.
     * @var Transaction[]
     * @required
     */
    public array $transactions;
}
