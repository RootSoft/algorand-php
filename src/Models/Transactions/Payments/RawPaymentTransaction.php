<?php


namespace Rootsoft\Algorand\Models\Transactions\Payments;

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Facades\AlgorandFacade;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;
use Rootsoft\Algorand\Utils\AlgorandUtils;

class RawPaymentTransaction extends RawTransaction
{

    /**
     * The address of the account that receives the amount.
     *
     * @var Address|null
     */
    public ?Address $receiver = null;

    /**
     * The total amount to be sent in microAlgos.
     *
     * @var BigInteger|null
     */
    public ?BigInteger $amount = null;

    /**
     * When set, it indicates that the transaction is requesting that the Sender account should be closed,
     * and all remaining funds, after the fee and amount are paid, be transferred to this address.
     *
     * @var string|null
     */
    public ?string $closeRemainderTo = null;

    public function toArray()
    {
        $transaction = parent::toArray();

        $paymentFields = [
            'amt' => $this->amount->toInt(),
            'rcv' => $this->receiver->address,
        ];

        return AlgorandUtils::algorand_array_clean(array_merge($transaction, $paymentFields));
    }
}
