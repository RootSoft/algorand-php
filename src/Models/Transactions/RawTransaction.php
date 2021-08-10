<?php


namespace Rootsoft\Algorand\Models\Transactions;

use Brick\Math\BigInteger;
use MessagePack\Type\Bin;
use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\Halite\Asymmetric\SignatureSecretKey;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\Builders\RawTransactionBuilder;
use Rootsoft\Algorand\Utils\Encoder;
use SodiumException;

/**
 * A raw serializable transaction class, used to generate transactions to broadcast to the network.
 * This is distinct from algod.model.RawTransaction, which is only returned for GET requests to algod.
 *
 * Algorand's msgpack encoding follows to following rules -
 *  1. Every integer must be encoded to the smallest type possible (0-255->8bit, 256-65535->16bit, etx)
 *  2. All fields names must be sorted
 *  3. All empty and 0 fields should be omitted
 *  4. Every positive number must be encoded as uint
 *  5. Binary blob should be used for binary data and string for strings
 *
 * TODO Rename to BaseTransaction
 * Class RawTransaction
 * @package Rootsoft\Algorand\Models\Transactions
 */
class RawTransaction
{
    /**
     * The minimum transaction fees (in micro algos).
     */
    const MIN_TX_FEE_UALGOS = 1000;

    /**
     * Paid by the sender to the FeeSink to prevent denial-of-service.
     * The minimum fee on Algorand is currently 1000 microAlgos.
     * This field cannot be combined with flat fee.
     *
     * @var ?BigInteger|null
     */
    private ?BigInteger $fee = null;

    /**
     * The first round for when the transaction is valid.
     * If the transaction is sent prior to this round it will be rejected by the network.
     *
     * @var ?BigInteger|null
     */
    public ?BigInteger $firstValid = null;

    /**
     * The hash of the genesis block of the network for which the transaction is valid.
     * See the genesis hash for MainNet, TestNet, and BetaNet.
     *
     * @var string|null
     */
    public ?string $genesisHash = null;

    /**
     * The ending round for which the transaction is valid.
     * After this round, the transaction will be rejected by the network.
     *
     * @var ?BigInteger
     */
    public ?BigInteger $lastValid = null;

    /**
     * The address of the account that pays the fee and amount.
     *
     * @var Address|null
     */
    public ?Address $sender = null;

    /**
     * Specifies the type of transaction.
     * This value is automatically generated using any of the developer tools.
     *
     * @var string|null
     */
    public ?string $type = null;

    /**
     * The human-readable string that identifies the network for the transaction.
     * The genesis ID is found in the genesis block.
     *
     * See the genesis ID for MainNet, TestNet, and BetaNet.
     *
     * @var string|null
     */
    public ?string $genesisId = null;

    /**
     * The group specifies that the transaction is part of a group and, if so,
     * specifies the hash of the transaction group.
     *
     * Assign a group ID to a transaction through the workflow described in the Atomic Transfers Guide.
     *
     * @var string|null
     */
    public ?string $group = null;

    /**
     * A lease enforces mutual exclusion of transactions.
     * If this field is nonzero, then once the transaction is confirmed, it acquires the lease identified by the
     * (Sender, Lease) pair of the transaction until the LastValid round passes.
     *
     * While this transaction possesses the lease, no other transaction specifying this lease can be confirmed.
     * A lease is often used in the context of Algorand Smart Contracts to prevent replay attacks.
     *
     * Read more about Algorand Smart Contracts and see the Delegate Key Registration TEAL template for an example
     * implementation of leases. Leases can also be used to safeguard against unintended duplicate spends.
     *
     * @var string|null
     */
    public ?string $lease = null;

    /**
     * Any data up to 1000 bytes.
     *
     * @var string|null
     */
    public ?string $note = null;

    /**
     * Specifies the authorized address.
     * This address will be used to authorize all future transactions. Learn more about Rekeying accounts.
     *
     * @var string|null
     */
    public ?string $rekeyTo = null;

    /**
     * Export the (encoded) transaction.
     *
     * @param $fileName
     */
    public function export($fileName)
    {
        file_put_contents($fileName, $this->getEncodedTransaction());
    }

    /**
     * Assign a group id to this transaction.
     * @param string $groupId
     */
    public function assignGroupID(string $groupId)
    {
        $this->group = $groupId;
    }

    /**
     * Sign the transaction with the given account.
     *
     * If the SK's corresponding address is different than the txn sender's, the SK's
     * corresponding address will be assigned as AuthAddr
     * https://github.com/algorand/js-algorand-sdk/blob/develop/src/transaction.js
     * https://github.com/algorand/java-algorand-sdk/blob/8064c4ee75b18ce0fdd8e5fab498bc20bd5e3c4e/src/main/java/com/algorand/algosdk/transaction/Transaction.java#L1214
     *
     * @param Account $account
     * @return SignedTransaction
     * @throws SodiumException
     * @throws AlgorandException
     */
    public function sign(Account $account)
    {
        $secretKey = $account->getPrivateKeyPair()->getSecretKey();
        if (! ($secretKey instanceof SignatureSecretKey)) {
            throw new AlgorandException('Private key is not a valid signing key.');
        }

        // Get the encoded transaction
        $encodedTx = $this->getEncodedTransaction();

        // Sign the transaction with secret key
        $signature = \sodium_crypto_sign_detached(
            $encodedTx,
            $secretKey->getRawKeyMaterial()
        );

        $signedTransaction = new SignedTransaction($signature, $this);

        if ($this->sender != $account->getAddress()) {
            $signedTransaction->setAuthAddr($account->getAddress());
        }

        return $signedTransaction;
    }

    /**
     * Get the encoded representation of the transaction with a prefix suitable for signing.
     * @return string
     */
    public function getEncodedTransaction()
    {
        // Encode transaction as msgpack
        $encodedTx = Encoder::getInstance()->encodeMessagePack($this->toMessagePack());

        // Prepend the transaction prefix
        $txBytes = (implode(unpack("H*", 'TX')));
        $encodedTx = hex2bin($txBytes) . $encodedTx;

        return $encodedTx;
    }

    /**
     * Get the transaction id.
     * The encoded transaction is hashed using sha512/256 and base32 encoded.
     *
     * @return string The id of the transaction.
     */
    public function getTransactionId()
    {
        // Hash the encoded transaction
        $txIdBytes = hash('sha512/256', $this->getEncodedTransaction(), true);

        // Encode with base32
        return Base32::encodeUpperUnpadded($txIdBytes);
    }

    /**
     * Get the binary representation of the transaction id.
     * The encoded transaction is hashed using sha512/256 without base32 encoding
     *
     * @return string
     */
    public function getRawTransactionId()
    {
        return hash('sha512/256', $this->getEncodedTransaction(), true);
    }

    /**
     * @return BigInteger|null
     */
    public function getFee(): ?BigInteger
    {
        return $this->fee;
    }

    /**
     * Set the flat fee.
     * This value will be used for the transaction fee, or 1000, whichever is higher.
     * This field cannot be combined with fee.
     *
     * @param BigInteger|null $fee
     */
    public function setFee(?BigInteger $fee): void
    {
        if ($fee == null) {
            $this->fee = BigInteger::of(self::MIN_TX_FEE_UALGOS);
        }

        $this->fee = $fee;
    }

    public function toMessagePack(): array
    {
        return [
            'fee' => $this->fee->toInt(),
            'fv' => $this->firstValid->toInt(),
            'lv' => $this->lastValid->toInt(),
            'note' => $this->note ? new Bin(utf8_encode($this->note)) : null,
            'snd' => $this->sender->address,
            'type' => $this->type,
            'gen' => $this->genesisId,
            'gh' => Base64::decode($this->genesisHash),
            'lx' => $this->lease,
            'grp' => $this->group ? new Bin($this->group) : null,
        ];
    }
}
