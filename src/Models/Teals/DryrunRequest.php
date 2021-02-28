<?php


namespace Rootsoft\Algorand\Models\Teals;

use Rootsoft\Algorand\Models\Account;
use Rootsoft\Algorand\Models\Application;

/**
 * Request data type for dryrun endpoint.
 * Given the Transactions and simulated ledger state upload, run TEAL scripts and return debugging information.
 * Class DryrunRequest
 * @package Rootsoft\Algorand\Models\Teals
 */
class DryrunRequest
{
    /**
     *
     * @var Account[]
     */
    public array $accounts = [];

    /**
     *
     * @var Application[]
     */
    public array $apps;

    /**
     * LatestTimestamp is available to some TEAL scripts.
     *
     * Defaults to the latest confirmed timestamp this algod is attached to.
     *
     * TODO int64 - long
     * @var int
     */
    public int $latestTimestamp;

    /**
     * ProtocolVersion specifies a specific version string to operate under,
     * otherwise whatever the current protocol of the network this algod is running in.
     *
     * @var string
     */
    public string $protocolVersion;

    /**
     * Round is available to some TEAL scripts.
     * Defaults to the current round on the network this algod is attached to.
     *
     * @var int
     */
    public int $round;

    /**
     * @var DryrunSource[]
     */
    public array $sources;

    /**
     * @var array
     */
    public array $txns;
}
