<?php


namespace Rootsoft\Algorand\Models\Transactions\Builders;

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\TransactionType;
use Rootsoft\Algorand\Models\Transactions\Types\RawPaymentTransaction;

/**
 * TODO Use generics
 *
 * Class PaymentTransactionBuilder
 * @package Rootsoft\Algorand\Models\Transactions\Builders
 */
class PaymentTransactionBuilder extends RawTransactionBuilder
{
    protected RawPaymentTransaction $paymentTransaction;

    /**
     * PaymentTransactionBuilder constructor.
     */
    public function __construct()
    {
        $this->paymentTransaction = new RawPaymentTransaction();
        parent::__construct(TransactionType::PAYMENT(), $this->paymentTransaction);
    }

    /**
     * When set, it indicates that the transaction is requesting that the Sender account should be closed, and all
     * remaining funds, after the fee and amount are paid, be transferred to this address.
     *
     * @param Address|null $closeRemainderTo
     * @return $this
     */
    public function closeRemainderTo(?Address $closeRemainderTo): PaymentTransactionBuilder
    {
        $this->paymentTransaction->closeRemainderTo = $closeRemainderTo;

        return $this;
    }

    /**
     * The address of the account that receives the amount.
     *
     * @param Address|null $receiver
     * @return $this
     */
    public function receiver(?Address $receiver): PaymentTransactionBuilder
    {
        $this->paymentTransaction->receiver = $receiver;

        return $this;
    }

    /**
     * The total amount to be sent in microAlgos.
     * Amounts are returned in microAlgos - the base unit for Algos.
     * Micro denotes a unit x 10^-6. Therefore, 1 Algo equals 1,000,000 microAlgos.
     *
     * @param int|null $amount
     * @return $this
     */
    public function amount(?int $amount): PaymentTransactionBuilder
    {
        $this->paymentTransaction->amount = BigInteger::of($amount);

        return $this;
    }

    /**
     * The total amount to be sent in microAlgos.
     * Amounts are returned in microAlgos - the base unit for Algos.
     * Micro denotes a unit x 10^-6. Therefore, 1 Algo equals 1,000,000 microAlgos.
     *
     * @param BigInteger $amount
     * @return $this
     */
    public function bigAmount(BigInteger $amount): PaymentTransactionBuilder
    {
        $this->paymentTransaction->amount = $amount;

        return $this;
    }

    /**
     * @return RawPaymentTransaction
     * @throws AlgorandException
     */
    public function build()
    {
        parent::build();

        return $this->paymentTransaction;
    }
}
