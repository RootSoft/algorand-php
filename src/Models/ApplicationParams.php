<?php

namespace Rootsoft\Algorand\Models;

/**
 * The global information associated with an application.
 * Class ApplicationParams.
 */
class ApplicationParams
{
    /**
     * The approval program.
     * @var string|null
     * @required
     */
    public ?string $approvalProgram = null;

    /**
     * The approval program.
     * @var string|null
     * @required
     */
    public ?string $clearStateProgram = null;

    /**
     * The address that created this application.
     *
     * This is the address where the parameters and global state for this application can be found.
     * @var string|null
     */
    public ?string $creator = null;

    /**
     * Global schema.
     * @var TealKeyValueStore[]|null
     */
    public ?array $globalState = null;

    /**
     * Global schema.
     * @var ApplicationStateSchema|null
     */
    public ?ApplicationStateSchema $globalStateSchema = null;

    /**
     * Local schema.
     * @var ApplicationStateSchema|null
     */
    public ?ApplicationStateSchema $localStateSchema = null;
}
