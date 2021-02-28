<?php


namespace Rootsoft\Algorand\Models\Accounts;


/**
 *
 * Class SearchAccountsResult
 * @package Rootsoft\Algorand\Models\Accounts
 */
class SearchAccountsResult
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
     * A list of accounts.
     * @var  \Rootsoft\Algorand\Models\Accounts\AccountInformation[]
     * @required
     */
    public array $accounts = [];

    /**
     * A list of asset holdings.
     * @var \Rootsoft\Algorand\Models\MiniAssetHolding[]
     */
    public array $balances = [];
}
