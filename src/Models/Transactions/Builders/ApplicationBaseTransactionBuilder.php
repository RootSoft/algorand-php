<?php


namespace Rootsoft\Algorand\Models\Transactions\Builders;

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Applications\OnCompletion;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;
use Rootsoft\Algorand\Models\Transactions\TransactionType;
use Rootsoft\Algorand\Models\Transactions\Types\ApplicationBaseTransaction;

class ApplicationBaseTransactionBuilder extends RawTransactionBuilder
{
    private ApplicationBaseTransaction $applicationTransaction;

    /**
     * ApplicationBaseTransactionBuilder constructor.
     */
    public function __construct(
        ?ApplicationBaseTransaction $applicationTransaction = null,
        ?OnCompletion $onCompletion = null
    ) {
        $this->applicationTransaction = $applicationTransaction ?? new ApplicationBaseTransaction();
        $this->applicationTransaction->onCompletion = $onCompletion ?? OnCompletion::NO_OP_OC();
        parent::__construct(TransactionType::APPLICATION_CALL(), $this->applicationTransaction);
    }

    /**
     * Append and overwrite an existing transaction to this one.
     *
     * @param RawTransaction $transaction
     * @return $this
     * @throws AlgorandException
     */
    public function append(RawTransaction $transaction): ApplicationBaseTransactionBuilder
    {
        parent::append($transaction);
        if ($transaction instanceof ApplicationBaseTransaction) {
            $this->applicationId($transaction->applicationId);
            $this->onCompletion($transaction->onCompletion);
            $this->arguments($transaction->arguments);
            $this->accounts($transaction->accounts);
            $this->foreignApps($transaction->foreignApps);
            $this->foreignAssets($transaction->foreignAssets);
        }

        return $this;
    }

    /**
     * ApplicationID is the application being interacted with,
     * or 0 if creating a new application.
     *
     * @param BigInteger|null $applicationId
     *
     * @return $this
     * @throws AlgorandException
     */
    public function applicationId(?BigInteger $applicationId): ApplicationBaseTransactionBuilder
    {
        if (is_null($applicationId)) {
            return $this;
        }

        if ($applicationId->isLessThan(0)) {
            throw new AlgorandException('Application id cant be smaller than 0');
        }

        $this->applicationTransaction->applicationId = $applicationId;

        return $this;
    }

    /**
     * Defines what additional actions occur with the transaction.
     * See the OnComplete section of the TEAL spec for details.
     *
     * @param OnCompletion|null $onCompletion
     * @return $this
     */
    public function onCompletion(?OnCompletion $onCompletion): ApplicationBaseTransactionBuilder
    {
        $this->applicationTransaction->onCompletion = $onCompletion;

        return $this;
    }

    /**
     * Transaction specific arguments accessed from the application's
     * approval-program and clear-state-program.
     *
     * @param array|null $arguments
     * @return $this
     */
    public function arguments(?array $arguments): ApplicationBaseTransactionBuilder
    {
        $this->applicationTransaction->arguments = $arguments;

        return $this;
    }

    /**
     * List of accounts in addition to the sender that may be accessed from the
     * application's approval-program and clear-state-program.
     *
     * @param array|null $accounts
     * @return $this
     */
    public function accounts(?array $accounts): ApplicationBaseTransactionBuilder
    {
        if (is_null($accounts) || empty($accounts)) {
            $accounts = null;
        }

        $this->applicationTransaction->accounts = $accounts;

        return $this;
    }

    /**
     * Lists the applications in addition to the application-id whose global states may be accessed by this
     * application's approval-program and clear-state-program. The access is read-only.
     *
     * @param array|null $foreignApps
     * @return $this
     */
    public function foreignApps(?array $foreignApps): ApplicationBaseTransactionBuilder
    {
        $this->applicationTransaction->foreignApps = $foreignApps;

        return $this;
    }

    /**
     * Lists the assets whose AssetParams may be accessed by this application's approval-program and
     * clear-state-program. The access is read-only.
     *
     * @param array|null $foreignAssets
     * @return $this
     */
    public function foreignAssets(?array $foreignAssets): ApplicationBaseTransactionBuilder
    {
        $this->applicationTransaction->foreignAssets = $foreignAssets;

        return $this;
    }

    public function estimateTransactionSize(): int
    {
        return 0;
    }

    /**
     * @return ApplicationBaseTransaction
     * @throws AlgorandException
     */
    public function build()
    {
        parent::build();

        // return ApplicationBaseTransaction::builder($this);
        return $this->applicationTransaction;
    }
}
