<?php

namespace Rootsoft\Algorand\Models\Applications;

use Rootsoft\Algorand\Models\Application;

class ApplicationResult
{
    /**
     * Application
     * @var Application
     */
    public Application $application;

    /**
     * Round at which the results were computed.
     * @var int|null
     */
    public ?int $currentRound = null;
}
