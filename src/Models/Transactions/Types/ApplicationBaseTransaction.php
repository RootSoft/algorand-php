<?php


namespace Rootsoft\Algorand\Models\Transactions\Types;

use Brick\Math\BigInteger;
use MessagePack\Type\Bin;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Applications\OnCompletion;
use Rootsoft\Algorand\Models\Transactions\Builders\ApplicationBaseTransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;

class ApplicationBaseTransaction extends RawTransaction
{

    /**
     * ApplicationID is the application being interacted with,
     * or 0 if creating a new application.
     *
     * @var BigInteger
     */
    public BigInteger $applicationId;

    /**
     * Defines what additional actions occur with the transaction.
     * See the OnComplete section of the TEAL spec for details.
     *
     * @var OnCompletion|null
     */
    public ?OnCompletion $onCompletion = null;

    /**
     * Transaction specific arguments accessed from the application's
     * approval-program and clear-state-program.
     *
     * @var array|null
     */
    public ?array $arguments = null;

    /**
     * List of accounts in addition to the sender that may be accessed from the
     * application's approval-program and clear-state-program.
     *
     * @var array|null
     */
    public ?array $accounts = null;

    /**
     * Lists the applications in addition to the application-id whose global states may be accessed by this
     * application's approval-program and clear-state-program. The access is read-only.
     *
     * @var array|null
     */
    public ?array $foreignApps = null;

    /**
     * Lists the assets whose AssetParams may be accessed by this application's approval-program and
     * clear-state-program. The access is read-only.
     *
     * @var array|null
     */
    public ?array $foreignAssets = null;

    /**
     *
     */
    public function __construct()
    {
        $this->applicationId = BigInteger::zero();
    }

    /**
     * Create a new application base transaction builder.
     *
     * @return ApplicationBaseTransactionBuilder
     */
    public static function createBuilder(): ApplicationBaseTransactionBuilder
    {
        return new ApplicationBaseTransactionBuilder();
    }

    /**
     * Create a new application from a builder.
     *
     * @param ApplicationBaseTransactionBuilder $builder
     * @return ApplicationBaseTransaction
     */
    public static function builder(ApplicationBaseTransactionBuilder $builder): ApplicationBaseTransaction
    {
        $transaction = new ApplicationBaseTransaction();

        return $transaction;
    }

    public function toMessagePack(): array
    {
        $fields = parent::toMessagePack();
        $fields['apid'] = $this->applicationId->toInt();
        $fields['apan'] = $this->onCompletion->getValue();
        $fields['apaa'] = ! empty($this->arguments) ? array_map(fn ($value) => new Bin($value), $this->arguments) : null;
        $fields['apat'] = ! empty($this->accounts) ? array_map(fn (Address $address) => $address->address, $this->accounts) : null;
        $fields['apfa'] = $this->foreignApps;
        $fields['apas'] = $this->foreignAssets;

        return $fields;
    }
}
