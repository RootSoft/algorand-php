<?php


namespace Rootsoft\Algorand\Models\Transactions\Builders;

use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Applications\OnCompletion;
use Rootsoft\Algorand\Models\Applications\TEALProgram;
use Rootsoft\Algorand\Models\Transactions\Types\ApplicationUpdateTransaction;

class ApplicationUpdateTransactionBuilder extends ApplicationBaseTransactionBuilder
{
    private ApplicationUpdateTransaction $applicationTransaction;

    /**
     * ApplicationBaseTransactionBuilder constructor.
     */
    public function __construct(
        ?ApplicationUpdateTransaction $applicationTransaction = null,
        ?OnCompletion $onCompletion = null
    ) {
        $this->applicationTransaction = $applicationTransaction ?? new ApplicationUpdateTransaction();
        $this->applicationTransaction->onCompletion = $onCompletion ?? OnCompletion::UPDATE_APPLICATION_OC();
        parent::__construct($this->applicationTransaction, $this->applicationTransaction->onCompletion);
    }

    /**
     * Logic executed for every application transaction, except when on-completion is set to "clear".
     * It can read and write global state for the application, as well as account-specific local state.
     * Approval programs may reject the transaction.
     *
     * @param TEALProgram $approvalProgram
     * @return $this
     */
    public function approvalProgram(TEALProgram $approvalProgram): ApplicationUpdateTransactionBuilder
    {
        $this->applicationTransaction->approvalProgram = $approvalProgram;

        return $this;
    }

    /**
     * Logic executed for application transactions with on-completion set to "clear".
     * It can read and write global state for the application, as well as  account-specific local state.
     * Clear state programs cannot reject the transaction.
     *
     * @param TEALProgram $clearStateProgram
     * @return $this
     */
    public function clearStateProgram(TEALProgram $clearStateProgram): ApplicationUpdateTransactionBuilder
    {
        $this->applicationTransaction->clearStateProgram = $clearStateProgram;

        return $this;
    }

    /**
     * @return ApplicationUpdateTransaction
     * @throws AlgorandException
     */
    public function build()
    {
        parent::build();

        return $this->applicationTransaction;
    }
}
