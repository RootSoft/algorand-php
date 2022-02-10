<?php

namespace Rootsoft\Algorand\Models\Transactions;

use Rootsoft\Algorand\Models\Applications\StateSchema;

class ApplicationTransactionResult
{
    /**
     * List of accounts in addition to the sender that may be accessed from the application's approval-program and
     * clear-state-program.
     * @var array|String[]
     */
    public array $accounts = [];

    /**
     * Transaction specific arguments accessed from the application's approval-program and clear-state-program.
     * @var array|String[]
     */
    public array $applicationArgs = [];

    /**
     * ID of the application being configured or empty if creating.
     * @var int
     */
    public int $applicationId;

    /**
     * Logic executed for every application transaction, except when on-completion is set to "clear".
     * It can read and write global state for the application, as well as account-specific local state.
     * Approval programs may reject the transaction.
     * @var string|null
     */
    public ?string $approvalProgram = null;

    /**
     * Logic executed for application transactions with on-completion set to "clear".
     * It can read and write global state for the application, as well as account-specific local state.
     * Clear state programs cannot reject the transaction.
     * @var string|null
     */
    public ?string $clearStateProgram = null;

    /**
     * Specifies the additional app program len requested in pages.
     * Defaults to 0
     * @var int
     */
    public int $extraProgramPages = 0;

    /**
     * Lists the applications in addition to the application-id whose global states may be accessed by this
     * application's approval-program and clear-state-program. The access is read-only.
     * @var array|int[]
     */
    public array $foreignApps = [];

    /**
     * Lists the assets whose parameters may be accessed by this application's ApprovalProgram and ClearStateProgram.
     * The access is read-only.
     * @var array|int[]
     */
    public array $foreignAssets = [];

    public ?StateSchema $globalStateSchema = null;

    public ?StateSchema $localStateSchema = null;

    public ?string $onCompletion = null;
}
