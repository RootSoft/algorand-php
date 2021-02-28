<?php


namespace Rootsoft\Algorand\Models\Accounts;

use Rootsoft\Algorand\Models\AccountParticipation;
use Rootsoft\Algorand\Models\ApplicationStateSchema;
use Rootsoft\Algorand\Models\Assets\Asset;

/**
 * Accounts information at a given round.
 * Class Accounts
 * @package Rootsoft\Algorand\Models
 */
class AccountInformation
{

    /**
     * The account public key
     * @var string
     * @required
     */
    public string $address;

    /**
     * Total number of MicroAlgos in the account
     * @var int
     * @required
     */
    public int $amount;

    /**
     * The amount of MicroAlgos in the account, without the pending rewards.
     * @var int
     * @required
     */
    public int $amountWithoutPendingRewards;

    /**
     * Applications local data stored in this account.
     * @var \Rootsoft\Algorand\Models\ApplicationLocalState[]
     */
    public array $appsLocalState;

    /**
     * Stores the sum of all of the local schemas and global schemas in this account.
     * @var \Rootsoft\Algorand\Models\ApplicationStateSchema|null
     * @optional
     */
    public ApplicationStateSchema $appsTotalSchema;

    /**
     * Assets held by this account.
     * @var \Rootsoft\Algorand\Models\AssetHolding[] assets
     */
    public array $assets;

    /**
     * The address against which signing should be checked.
     *
     * If empty, the address of the current account is used.
     * This field can be updated in any transaction by setting the RekeyTo field.
     *
     * @var string|null
     */
    public string $authAddr;

    /**
     * The parameter of applications created by this account, including app global data.
     * @var \Rootsoft\Algorand\Models\Application[] applications
     */
    public array $createdApps;

    /**
     * The parameters of assets created by this account.
     *
     * @var \Rootsoft\Algorand\Models\Assets\Asset[] assets
     */
    public array $createdAssets;

    /**
     * Describes the parameters used by this account in consensus protocol.
     * @var \Rootsoft\Algorand\Models\AccountParticipation|null
     */
    public AccountParticipation $participation;

    /**
     * The amount of MicroAlgos of pending rewards in this account.
     * @var int
     * @required
     */
    public int $pendingRewards;

    /**
     * Used as part of the rewards computation.
     * Only applicable to accounts which are participating.
     * @var int|null
     */
    public int $rewardBase;

    /**
     * The total rewards of MicroAlgos the account has received, including pending rewards.
     * @var int
     * @required
     */
    public int $rewards;

    /**
     * The round for which this information is relevant.
     * @var int
     * @required
     */
    public int $round;

    /**
     * Indicates what type of signature is used by this account, must be one of:
     *  * sig
     *  * msig
     *  * lsig
     * @var string|null
     */
    public string $sigType;

    /**
     * Delegation status of the account's MicroAlgos
     * Offline - indicates that the associated account is delegated.
     * Online - indicates that the associated account used as part of the delegation pool.
     * NotParticipating - indicates that the associated account is neither a delegator nor a delegate.
     * @var string
     * @required
     */
    public string $status;
}
