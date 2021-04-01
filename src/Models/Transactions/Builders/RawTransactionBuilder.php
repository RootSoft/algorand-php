<?php


namespace Rootsoft\Algorand\Models\Transactions\Builders;

use Brick\Math\BigInteger;
use Illuminate\Support\Arr;
use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;
use Rootsoft\Algorand\Models\Transactions\TransactionParams;
use Rootsoft\Algorand\Models\Transactions\TransactionType;
use Rootsoft\Algorand\Utils\AlgorandUtils;

/**
 * TODO Make transaction abstract
 *
 * Class RawTransactionBuilder
 * @package Rootsoft\Algorand\Models\Transactions\Builders
 */
abstract class RawTransactionBuilder
{

    /**
     * Paid by the sender to the FeeSink to prevent denial-of-service.
     * The minimum fee on Algorand is currently 1000 microAlgos.
     * This field cannot be combined with flat fee.
     *
     * @var ?BigInteger
     */
    private ?BigInteger $fee = null;

    /**
     * A suggested fee per byte.
     * The total fee is calculated by multiplying the entire size of the transaction times the suggested fee per byte.
     *
     * @var BigInteger|null
     */
    private ?BigInteger $suggestedFeePerByte = null;

    /**
     * This value will be used for the transaction fee, or 1000, whichever is higher.
     * This field cannot be combined with fee.
     * The minimum fee on Algorand is currently 1000 microAlgos.
     *
     * @var ?BigInteger
     */
    private ?BigInteger $flatFee = null;

    /** @var array */
    protected array $payload = [];

    /**
     *
     * @var RawTransaction
     */
    private RawTransaction $transaction;

    /**
     * @param TransactionType $type
     * @param RawTransaction|null $transaction
     */
    public function __construct(TransactionType $type, RawTransaction $transaction = null)
    {
        $this->transaction = $transaction ?? new RawTransaction();
        $this->transaction->type = $type;
    }

    /**
     * The address of the account that pays the fee and amount.
     *
     * @param Address $sender
     * @return $this
     */
    public function sender(Address $sender)
    {
        $this->transaction->sender = $sender;

        return $this;
    }

    /**
     * Any data up to 1000 bytes.
     * The note is Base64 encoded.
     *
     * @param ?string $note
     * @return $this
     */
    public function note(?string $note)
    {
        $this->transaction->note = $note;

        return $this;
    }

    /**
     * Set the fee per bytes value (in microAlgos).
     * This value is multiplied by the estimated size of the transaction to reach a final transaction fee, or 1000,
     * whichever is higher.
     * This field cannot be combined with flatFee.
     *
     * @param int $fee
     * @return $this
     */
    public function suggestedFeePerByte(int $fee)
    {
        $this->suggestedFeePerByte = BigInteger::of($fee);

        return $this;
    }

    /**
     * Set the flat fee (in microAlgos).
     * This value will be used for the transaction fee, or 1000, whichever is higher.
     * This field cannot be combined with fee.
     *
     * @param int $fee
     * @return $this
     */
    public function flatFee(int $fee)
    {
        $this->flatFee = BigInteger::of($fee);

        return $this;
    }

    public function suggestedParams(TransactionParams $params)
    {
        // TODO Rework
        $this->suggestedFeePerByte = BigInteger::of($params->fee);
        $this->transaction->setFee(BigInteger::of($params->fee));
        $this->transaction->genesisId = $params->genesisId;
        $this->transaction->genesisHash = $params->genesisHash;
        $this->transaction->firstValid = BigInteger::of($params->lastRound);
        $this->transaction->lastValid = BigInteger::of($params->lastRound + 1000);

        return $this;
    }

    public function useSuggestedParams(Algorand $algorand)
    {
        $params = $algorand->getSuggestedTransactionParams();
        return $this->suggestedParams($params);
    }

    /**
     * Set parameters.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setParameter(string $key, $value)
    {
        Arr::set($this->payload, $key, $value);

        return $this;
    }

    /**
     * Get parameters.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParameter(string $key, $default = null)
    {
        return Arr::get($this->payload, $key, $default);
    }

    /**
     * @return RawTransaction
     * @throws AlgorandException
     */
    public function build()
    {
        // Fee Validation
        if ($this->suggestedFeePerByte != null && $this->flatFee != null) {
            throw new AlgorandException("Cannot set both fee and flatFee.");
        }

        if ($this->suggestedFeePerByte != null) {
            $this->transaction->setFee(AlgorandUtils::calculate_fee_per_byte($this->transaction, $this->suggestedFeePerByte));
        } elseif ($this->flatFee != null) {
            $this->transaction->setFee($this->flatFee);
        }

        $fee = $this->transaction->getFee();
        if ($fee == null || $fee === BigInteger::of(0)) {
            $this->transaction->setFee(BigInteger::of(RawTransaction::MIN_TX_FEE_UALGOS));
        }

        return $this->transaction;
    }
}
