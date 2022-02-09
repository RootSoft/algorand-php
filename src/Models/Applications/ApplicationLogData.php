<?php

namespace Rootsoft\Algorand\Models\Applications;


class ApplicationLogData
{
    /**
     * Logs for the application being executed by the transaction.
     * @var string[]
     */
    public array $logs;

    /**
     * The transaction id.
     * @var string|null
     */
    public ?string $txid;
}
