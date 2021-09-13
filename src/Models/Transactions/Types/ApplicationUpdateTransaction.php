<?php

namespace Rootsoft\Algorand\Models\Transactions\Types;

use Rootsoft\Algorand\Models\Applications\TEALProgram;

class ApplicationUpdateTransaction extends ApplicationBaseTransaction
{
    /**
     * Logic executed for every application transaction, except when on-completion is set to "clear".
     * It can read and write global state for the application, as well as account-specific local state.
     * Approval programs may reject the transaction.
     *
     * @var TEALProgram|null
     */
    public ?TEALProgram $approvalProgram = null;

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
        $fields['apap'] = isset($this->approvalProgram) ? $this->approvalProgram->bytes() : null;
        $fields['apsu'] = isset($this->clearStateProgram) ? $this->clearStateProgram->bytes() : null;

        return $fields;
    }
}
