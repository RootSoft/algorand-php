<?php

namespace Rootsoft\Algorand\Models\Transactions\Types;

use Rootsoft\Algorand\Models\Applications\StateSchema;
use Rootsoft\Algorand\Models\Applications\TEALProgram;

class ApplicationCreateTransaction extends ApplicationUpdateTransaction
{
    /**
     * Holds the maximum number of local state values defined within aStateSchema object.
     *
     * @var StateSchema|null
     */
    public ?StateSchema $localStateSchema = null;

    /**
     * Holds the maximum number of global state values defined within aStateSchema object.
     *
     * @var StateSchema|null
     */
    public ?StateSchema $globalStateSchema = null;

    /**
     * Number of additional pages allocated to the application's approval and clear state programs.
     * Each ExtraProgramPages is 2048 bytes.
     *
     * The sum of ApprovalProgram and ClearStateProgram may not exceed 2048*(1+ExtraProgramPages) bytes.
     *
     * @var int|null
     */
    public ?int $extraPages = null;

    /**
     * Logic executed for application transactions with on-completion set to "clear".
     * It can read and write global state for the application, as well as  account-specific local state.
     * Clear state programs cannot reject the transaction.
     *
     * @var TEALProgram|null
     */
    public ?TEALProgram $clearStateProgram = null;

    public function toMessagePack(): array
    {
        $fields = parent::toMessagePack();
        $fields['apls'] = $this->localStateSchema;
        $fields['apgs'] = $this->globalStateSchema;
        $fields['apep'] = $this->extraPages;

        return $fields;
    }
}
