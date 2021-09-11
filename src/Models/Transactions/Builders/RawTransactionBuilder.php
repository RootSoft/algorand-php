<?php


namespace Rootsoft\Algorand\Models\Transactions\Builders;

use Brick\Math\BigInteger;
use Illuminate\Support\Arr;
use ParagonIE\ConstantTime\Base64;
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
class RawTransactionBuilder
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
     * Append and overwrite an existing transaction to this one.
     *
     * @param RawTransaction $transaction
     * @return $this
     */
    public function append(RawTransaction $transaction): RawTransactionBuilder
    {
        $this->type($transaction->type);
        $this->flatFee($transaction->getFee()->toInt());
        $this->firstValid($transaction->firstValid->toInt());
        $this->lastValid($transaction->lastValid->toInt());
        $this->note($transaction->note);
        $this->sender($transaction->sender);
        $this->genesisId($transaction->genesisId);
        $this->genesisHash($transaction->genesisHash);
        $this->lease($transaction->lease);
        $this->group($transaction->group);
        $this->rekeyTo($transaction->rekeyTo);

        return $this;
    }

    /**
     * The address of the account that pays the fee and amount.
     *
     * @param Address|null $sender
     * @return $this
     */
    public function sender(?Address $sender)
    {
        $this->transaction->sender = $sender;

        return $this;
    }

    /**
     * Any data up to 1000 bytes.
     * This is the raw data.
     *
     * @param ?string $note
     * @return $this
     */
    public function note(?string $note): RawTransactionBuilder
    {
        $this->transaction->note = $note;

        return $this;
    }

    /**
     * Any data up to 1000 bytes.
     * The note should be UTF-8 text.
     *
     * @param ?string $note
     * @return $this
     */
    public function noteText(string $note): RawTransactionBuilder
    {
        $this->transaction->note = utf8_encode($note);

        return $this;
    }

    /**
     * Any data up to 1000 bytes.
     * The note should be Base64 encoded.
     *
     * @param ?string $note
     * @return $this
     */
    public function noteB64(string $note): RawTransactionBuilder
    {
        $this->transaction->note = Base64::decode($note);

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
    public function suggestedFeePerByte(int $fee): RawTransactionBuilder
    {
        $this->suggestedFeePerByte = BigInteger::of($fee);

        return $this;
    }

    /**
     * Set the flat fee (in microAlgos).
     * This value will be used for the transaction fee, or 1000, whichever is higher.
     * This field cannot be combined with fee.
     *
     * @param int|null $fee
     * @return $this
     */
    public function flatFee(?int $fee): RawTransactionBuilder
    {
        if ($fee == null) {
            return $this;
        }

        $this->flatFee = BigInteger::of($fee);

        return $this;
    }

    /**
     * The first round for when the transaction is valid.
     * If the transaction is sent prior to this round it will be rejected by the network.
     *
     *
     * @param int|null $firstValid
     * @return RawTransactionBuilder
     */
    public function firstValid(?int $firstValid): RawTransactionBuilder
    {
        $this->transaction->firstValid = BigInteger::of($firstValid);

        return $this;
    }

    /**
     * The ending round for which the transaction is valid.
     * After this round, the transaction will be rejected by the network.
     *
     *
     * @param int|null $lastValid
     * @return RawTransactionBuilder
     */
    public function lastValid(?int $lastValid): RawTransactionBuilder
    {
        $this->transaction->lastValid = BigInteger::of($lastValid);

        return $this;
    }

    /**
     * The human-readable string that identifies the network for the transaction.
     * The genesis ID is found in the genesis block.
     *
     * See the genesis ID for MainNet, TestNet, and BetaNet.
     *
     * @param string|null $genesisId
     * @return RawTransactionBuilder
     */
    public function genesisId(?string $genesisId): RawTransactionBuilder
    {
        $this->transaction->genesisId = $genesisId;

        return $this;
    }

    /**
     * The hash of the genesis block of the network for which the transaction is valid.
     * See the genesis hash for MainNet, TestNet, and BetaNet.
     *
     * @param string|null $genesisHash
     * @return RawTransactionBuilder
     */
    public function genesisHash(?string $genesisHash): RawTransactionBuilder
    {
        $this->transaction->genesisHash = $genesisHash;

        return $this;
    }

    /**
     * The hash of the genesis block of the network for which the transaction is valid.
     * See the genesis hash for MainNet, TestNet, and BetaNet.
     *
     * @param string $genesisHash
     * @return RawTransactionBuilder
     */
    public function genesisHashB64(string $genesisHash): RawTransactionBuilder
    {
        $this->transaction->genesisHash = Base64::decode($genesisHash);

        return $this;
    }

    /**
     * A lease enforces mutual exclusion of transactions.
     * If this field is nonzero, then once the transaction is confirmed, it acquires the lease identified by the
     * (Sender, Lease) pair of the transaction until the LastValid round passes.
     *
     * While this transaction possesses the lease, no other transaction specifying this lease can be confirmed.
     *
     * A lease is often used in the context of Algorand Smart Contracts to prevent replay attacks.
     * Read more about Algorand Smart Contracts and see the Delegate Key Registration TEAL template for an example
     * implementation of leases.
     *
     * Leases can also be used to safeguard against unintended duplicate spends.
     *
     * @param string|null $lease
     * @return RawTransactionBuilder
     */
    public function lease(?string $lease): RawTransactionBuilder
    {
        $this->transaction->lease = $lease;

        return $this;
    }

    /**
     * A lease enforces mutual exclusion of transactions.
     * If this field is nonzero, then once the transaction is confirmed, it acquires the lease identified by the
     * (Sender, Lease) pair of the transaction until the LastValid round passes.
     *
     * While this transaction possesses the lease, no other transaction specifying this lease can be confirmed.
     *
     * A lease is often used in the context of Algorand Smart Contracts to prevent replay attacks.
     * Read more about Algorand Smart Contracts and see the Delegate Key Registration TEAL template for an example
     * implementation of leases.
     *
     * Leases can also be used to safeguard against unintended duplicate spends.
     *
     * @param string $lease
     * @return RawTransactionBuilder
     */
    public function leaseB64(string $lease): RawTransactionBuilder
    {
        $this->transaction->lease = Base64::decode($lease);

        return $this;
    }

    /**
     * The group specifies that the transaction is part of a group and, if so,
     * specifies the hash of the transaction group.
     *
     * Assign a group ID to a transaction through the workflow described in the Atomic Transfers Guide.
     *
     *
     * @param string|null $group
     * @return RawTransactionBuilder
     */
    public function group(?string $group): RawTransactionBuilder
    {
        $this->transaction->group = $group;

        return $this;
    }

    /**
     * Specifies the type of transaction.
     * This value is automatically generated using any of the developer tools.
     *
     * @param string|null $type
     * @return RawTransactionBuilder
     */
    public function type(?string $type): RawTransactionBuilder
    {
        $this->transaction->type = $type;

        return $this;
    }

    /**
     * Specifies the authorized address. This address will be used to authorize all future transactions.
     * Rekeying is a powerful protocol feature which enables an Algorand account holder to maintain a static public
     * address while dynamically rotating the authoritative private spending key(s).
     *
     * @param Address|null $address
     * @return $this
     */
    public function rekeyTo(?Address $address): RawTransactionBuilder
    {
        $this->transaction->rekeyTo = $address;

        return $this;
    }

    public function suggestedParams(TransactionParams $params)
    {
        // TODO Rework
        $this->suggestedFeePerByte = BigInteger::of($params->fee);
        $this->transaction->setFee(BigInteger::of($params->fee));
        $this->transaction->genesisId = $params->genesisId;
        $this->transaction->genesisHash = Base64::decode($params->genesisHash);
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
     * @return BigInteger|null
     */
    public function getFee(): ?BigInteger
    {
        return $this->fee;
    }

    /**
     * @return RawTransaction
     * @throws AlgorandException
     */
    public function build()
    {
        // TODO Work transaction out.

        // Fee Validation
        if ($this->suggestedFeePerByte != null && $this->flatFee != null) {
            throw new AlgorandException("Cannot set both fee and flatFee.");
        }

        if ($this->suggestedFeePerByte != null) {
            // Set the fee to calculate correct estimated transaction size
            // see setFeeByFeePerByte in Java
            $this->transaction->setFee($this->suggestedFeePerByte);

            // Calculate the fee
            $fee = AlgorandUtils::calculate_fee_per_byte($this->transaction, $this->suggestedFeePerByte);
            $this->transaction->setFee($fee);
        } elseif ($this->flatFee != null) {
            $this->transaction->setFee($this->flatFee);
        }

        $fee = $this->transaction->getFee();
        if ($fee == null || $fee === BigInteger::of(0)) {
            $this->transaction->setFee(BigInteger::of(RawTransaction::MIN_TX_FEE_UALGOS));
        }

        $this->fee = $this->transaction->getFee();

        return $this->transaction;
    }
}
