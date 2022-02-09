<?php

namespace Rootsoft\Algorand\Models\Applications;

class ApplicationLogsResult
{
    /**
     * Application index
     * @var int|null
     */
    public ?int $applicationId;

    /**
     * Round at which the results were computed.
     * @var int|null
     */
    public ?int $currentRound;

    /**
     * A list of assets.
     * @var ApplicationLogData[]
     */
    public array $logData;

    public ?string $nextToken;
}
