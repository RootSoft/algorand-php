<?php

namespace Rootsoft\Algorand\Models;

class ApplicationLocalState
{
    /**
     * The application which this local state is for.
     *
     * @var int
     */
    public int $id;

    /**
     * The schema (required).
     *
     * @var \Rootsoft\Algorand\Models\ApplicationStateSchema
     */
    public ApplicationStateSchema $schema;

    /**
     * Round when account closed out of the application.
     *
     * @var int|null
     */
    public ?int $closedOutAtRound = null;

    /**
     * Whether the application local state is currently deleted from its account..
     *
     * @var bool|null
     */
    public ?bool $deleted = null;

    /**
     * storage.
     * @var \Rootsoft\Algorand\Models\TealKeyValueStore[]
     */
    public array $keyValue;

    /**
     * Round when the account opted into the application.
     *
     * @var int|null
     */
    public ?int $optedInAtRound = null;
}
