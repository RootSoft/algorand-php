<?php

namespace Rootsoft\Algorand\Models\Transactions\Builders;

use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Applications\OnCompletion;
use Rootsoft\Algorand\Models\Applications\StateSchema;
use Rootsoft\Algorand\Models\Transactions\Types\ApplicationCreateTransaction;

class ApplicationCreateTransactionBuilder extends ApplicationUpdateTransactionBuilder
{
    private ApplicationCreateTransaction $applicationTransaction;

    /**
     * ApplicationCreateTransactionBuilder constructor.
     */
    public function __construct()
    {
        $this->applicationTransaction = new ApplicationCreateTransaction();
        parent::__construct($this->applicationTransaction, OnCompletion::NO_OP_OC());
    }

    /**
     * Holds the maximum number of local state values defined within aStateSchema object.
     *
     * @param StateSchema|null $localStateSchema
     * @return $this
     */
    public function localStateSchema(?StateSchema $localStateSchema): self
    {
        $this->applicationTransaction->localStateSchema = $localStateSchema;

        return $this;
    }

    /**
     * Holds the maximum number of global state values defined within aStateSchema object.
     *
     * @param StateSchema|null $globalStateSchema
     * @return $this
     */
    public function globalStateSchema(?StateSchema $globalStateSchema): self
    {
        $this->applicationTransaction->globalStateSchema = $globalStateSchema;

        return $this;
    }

    /**
     * Number of additional pages allocated to the application's approval and clear state programs.
     * Each ExtraProgramPages is 2048 bytes.
     *
     * The sum of ApprovalProgram and ClearStateProgram may not exceed 2048*(1+ExtraProgramPages) bytes.
     *
     * @param int|null $extraPages
     * @return ApplicationCreateTransactionBuilder
     */
    public function extraPages(?int $extraPages): self
    {
        $this->applicationTransaction->extraPages = $extraPages;

        return $this;
    }

    /**
     * @return ApplicationCreateTransaction
     * @throws AlgorandException
     */
    public function build() : ApplicationCreateTransaction
    {
        parent::build();

        // return ApplicationBaseTransaction::builder($this);
        return $this->applicationTransaction;
    }
}
