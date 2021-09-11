<?php


namespace Rootsoft\Algorand\Models\Transactions\Builders;

use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Applications\OnCompletion;
use Rootsoft\Algorand\Models\Applications\TEALProgram;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;
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
     * Append and overwrite an existing transaction to this one.
     *
     * @param RawTransaction $transaction
     * @return $this
     * @throws AlgorandException
     */
    public function append(RawTransaction $transaction): ApplicationUpdateTransactionBuilder
    {
        parent::append($transaction);
        if ($transaction instanceof ApplicationUpdateTransaction) {
            $this->approvalProgram($transaction->approvalProgram);
            $this->clearStateProgram($transaction->clearStateProgram);
        }

        return $this;
    }

    /**
     * Logic executed for every application transaction, except when on-completion is set to "clear".
     * It can read and write global state for the application, as well as account-specific local state.
     * Approval programs may reject the transaction.
     *
     * @param TEALProgram|null $approvalProgram
     * @return $this
     */
    public function approvalProgram(?TEALProgram $approvalProgram): ApplicationUpdateTransactionBuilder
    {
        $this->applicationTransaction->approvalProgram = $approvalProgram;

        return $this;
    }

    /**
     * Logic executed for application transactions with on-completion set to "clear".
     * It can read and write global state for the application, as well as  account-specific local state.
     * Clear state programs cannot reject the transaction.
     *
     * @param TEALProgram|null $clearStateProgram
     * @return $this
     */
    public function clearStateProgram(?TEALProgram $clearStateProgram): ApplicationUpdateTransactionBuilder
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
