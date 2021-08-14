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
     * The address of the account that will be rekeyed to.
     * Rekeying is a powerful protocol feature which enables an Algorand account holder to maintain a static public
     * address while dynamically rotating the authoritative private spending key(s).
     *
     * @param Address $receiver
     * @return $this
     */
    public function rekeyTo(Address $address)
    {
        $this->paymentTransaction->rekeyTo = $address;

        return $this;
    }

    /**
     * The address of the account that receives the amount.
     *
     * @param Address $receiver
     * @return $this
     */
    public function receiver(Address $receiver)
    {
        $this->paymentTransaction->receiver = $receiver;

        return $this;
    }

    /**
     * The total amount to be sent in microAlgos.
     * Amounts are returned in microAlgos - the base unit for Algos.
     * Micro denotes a unit x 10^-6. Therefore, 1 Algo equals 1,000,000 microAlgos.
     *
     * @param int $amount
     * @return $this
     */
    public function amount(int $amount)
    {
        $this->paymentTransaction->amount = BigInteger::of($amount);

        return $this;
    }

    /**
     * The total amount to be sent in microAlgos.
     * Amounts are returned in microAlgos - the base unit for Algos.
     * Micro denotes a unit x 10^-6. Therefore, 1 Algo equals 1,000,000 microAlgos.
     *
     * @param int $amount
     * @return $this
     */
    public function bigAmount(BigInteger $amount)
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
