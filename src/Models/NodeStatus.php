<?php


namespace Rootsoft\Algorand\Models;

/**
 * The current node status.
 * Class NodeStatus
 * @package Rootsoft\Algorand\Models
 */
class NodeStatus
{
    /**
     * The current catchpoint that is being caught up to.
     * @var string|null
     */
    public string $catchpoint;

    /**
     * The number of blocks that have already been obtained by the node as part of the catchup
     * @var int|null
     */
    public int $catchpointAcquiredBlocks;

    /**
     * The number of accounts from the current catchpoint that have been processed so far as part of the catchup.
     * @var int|null
     */
    public int $catchpointProcessedAccounts;

    /**
     * The total number of accounts included in the current catchpoint.
     * @var int|null
     */
    public int $catchpointTotalAccounts;

    /**
     * The total number of blocks that are required to complete the current catchpoint catchup
     * @var int|null
     */
    public int $catchpointTotalBlocks;

    /**
     * The number of accounts from the current catchpoint that have been verified so far as part of the catchup
     * @var int|null
     */
    public int $catchpointVerifiedAccounts;

    /**
     * CatchupTime in nanoseconds
     * @var int
     * @required
     */
    public int $catchupTime;

    /**
     * The last catchpoint seen by the node
     * @var string|null
     */
    public string $lastCatchpoint;

    /**
     * LastRound indicates the last round seen
     * @var int
     * @required
     */
    public int $lastRound;

    /**
     * LastVersion indicates the last consensus version supported
     * @var string
     * @required
     */
    public string $lastVersion;

    /**
     * NextVersion of consensus protocol to use
     * @var string
     * @required
     */
    public string $nextVersion;

    /**
     * NextVersionRound is the round at which the next consensus version will apply
     * @var int
     * @required
     */
    public int $nextVersionRound;

    /**
     * NextVersionSupported indicates whether the next consensus version is supported by this node
     * @var bool
     * @required
     */
    public bool $nextVersionSupported;

    /**
     * StoppedAtUnsupportedRound indicates that the node does not support the new rounds and has stopped making progress
     * @var bool
     * @required
     */
    public bool $stoppedAtUnsupportedRound;

    /**
     * TimeSinceLastRound in nanoseconds
     * @var int
     * @required
     */
    public int $timeSinceLastRound;
}
