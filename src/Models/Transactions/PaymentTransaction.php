<?php


namespace Rootsoft\Algorand\Models\Transactions;

/**
 * Fields for a payment transaction.
 *
 * Class PaymentTransaction
 * @package Rootsoft\Algorand\Models\Transactions
 */
class PaymentTransaction
{

    /**
     * Number of MicroAlgos intended to be transferred.
     * @var int
     * @required
     */
    public int $amount;

    /**
     * Number of MicroAlgos that were sent to the close-remainder-to address when closing the sender account.
     * @var int|null
     */
    public ?int $closeAmount = null;

    /**
     * When set, indicates that the sending account should be closed and all remaining funds be transferred to this address.
     * @var string|null
     */
    public ?string $closeRemainderTo = null;

    /**
     * Receivers's address.
     * @var string
     * @required
     */
    public string $receiver;
}
