<?php


namespace Rootsoft\Algorand\Models;

/**
 * The global information associated with an application.
 * Class ApplicationParams
 * @package Rootsoft\Algorand\Models
 */
class ApplicationParams
{
    /**
     * The approval program.
     * @var string
     * @required
     */
    public string $approvalProgram;

    /**
     * The approval program.
     * @var string
     * @required
     */
    public string $clearStateProgram;

    /**
     * The address that created this application.
     *
     * This is the address where the parameters and global state for this application can be found.
     * @var string
     * @required
     */
    public string $creator;

    /**
     * Global schema
     * @var TealKeyValueStore[]
     */
    public array $globalState;

    /**
     * Global schema
     * @var ApplicationStateSchema
     */
    public ApplicationStateSchema $globalStateSchema;

    /**
     * Local schema
     * @var ApplicationStateSchema
     */
    public ApplicationStateSchema $localStateSchema;
}
