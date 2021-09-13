<?php

namespace Rootsoft\Algorand\Models\Teals;

use Rootsoft\Algorand\Models\Accounts\AccountInformation;
use Rootsoft\Algorand\Models\Application;
use Rootsoft\Algorand\Utils\MessagePackable;

/**
 * Request data type for dryrun endpoint.
 * Given the Transactions and simulated ledger state upload, run TEAL scripts and return debugging information.
 * Class DryrunRequest.
 */
class DryrunRequest implements MessagePackable
{
    /**
     * @var AccountInformation[]
     */
    public array $accounts = [];

    /**
     * @var Application[]
     */
    public array $apps = [];

    /**
     * LatestTimestamp is available to some TEAL scripts.
     *
     * Defaults to the latest confirmed timestamp this algod is attached to.
     *
     * TODO int64 - long
     * @var int
     */
    public ?int $latestTimestamp = null;

    /**
     * ProtocolVersion specifies a specific version string to operate under,
     * otherwise whatever the current protocol of the network this algod is running in.
     *
     * @var string
     */
    public ?string $protocolVersion = null;

    /**
     * Round is available to some TEAL scripts.
     * Defaults to the current round on the network this algod is attached to.
     *
     * @var int
     */
    public ?int $round = null;

    /**
     * @var DryrunSource[]
     */
    public array $sources = [];

    /**
     * @var array
     */
    public array $txns = [];

    public function toMessagePack(): array
    {
        return [
            'accounts' => $this->accounts,
            'apps' => $this->apps,
            'latest-timestamp' => $this->latestTimestamp,
            'protocol-version' => $this->protocolVersion,
            'round' => $this->round,
            'sources' => $this->sources,
            'txns' => $this->txns,
        ];
    }
}
