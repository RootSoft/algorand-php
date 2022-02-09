<?php

namespace Rootsoft\Algorand\Models\Transactions;

use Rootsoft\Algorand\Models\Applications\AccountStateDelta;
use Rootsoft\Algorand\Models\Applications\EvalDeltaKeyValue;

/**
 * Contains all fields common to all transactions and serves as an envelope to all transactions type.
 *
 * https://developer.algorand.org/docs/reference/rest-apis/indexer/#transaction.
 *
 * Class Transaction
 */
class Transaction
{
    /**
     * Rewards applied to close-remainder-to account.
     * @var int|null
     */
    public ?int $closeRewards = null;

    /**
     * Closing amount for transaction.
     * @var int|null
     */
    public ?int $closingAmount = null;

    /**
     * Round when the transaction was confirmed.
     * @var int|null
     */
    public ?int $confirmedRound = null;

    /**
     * Specifies an application index (ID) if an application was created with this transaction.
     * @var int|null
     */
    public ?int $createdApplicationIndex = null;

    /**
     * The index of the created asset.
     * @var int|null
     */
    public ?int $createdAssetIndex = null;

    /**
     * Transaction fee.
     * @var int
     * @required
     */
    public int $fee;

    /**
     * First valid round for this transaction.
     * @var int
     * @required
     */
    public int $firstValid;

    /**
     * Hash of genesis block.
     * @var string|null
     */
    public ?string $genesisHash = null;

    /**
     * Genesis block ID.
     * @var string|null
     */
    public ?string $genesisId = null;

    /**
     * Global state key/value changes for the application being executed by this transaction.
     * @var array|EvalDeltaKeyValue
     */
    public array $globalStateDelta = [];

    /**
     * Local state key/value changes for the application being executed by this transaction.
     * @var array|AccountStateDelta[]
     */
    public array $localStateDelta = [];

    /**
     * Logs for the application being executed by this transaction.
     * @var array|string[]
     */
    public array $logs = [];

    /**
     * Base64 encoded byte array of a sha512/256 digest. When present indicates that this transaction is part of a
     * transaction group and the value is the sha512/256 hash of the transactions in that group.
     */
    public ?string $group = null;

    /**
     * Base64 encoded 32-byte array. Lease enforces mutual exclusion of transactions.
     * If this field is nonzero, then once the transaction is confirmed, it acquires the lease identified by the
     * (Sender, Lease) pair of the transaction until the LastValid round passes.
     *
     * While this transaction possesses the lease, no other transaction specifying this lease can be confirmed.
     */
    public ?string $lease = null;

    /**
     * Transaction ID.
     * @var string
     * @required
     */
    public string $id;

    /**
     * Offset into the round where this transaction was confirmed.
     * @var int|null
     */
    public ?int $intraRoundOffset = null;

    /**
     * Last valid round for this transaction.
     * @var int
     * @required
     */
    public int $lastValid;


    /**
     * Rewards applied to receiver account.
     * @var int|null
     */
    public ?int $receiverRewards = null;

    /**
     * Time when the block this transaction is in was confirmed.
     * @var int|null
     */
    public ?int $roundTime = null;

    /**
     * Sender's address.
     * @var string
     * @required
     */
    public string $sender;

    /**
     * Rewards applied to sender account.
     * @var int|null
     */
    public ?int $senderRewards = null;

    /**
     * Validation signature associated with some data.
     *
     * @var TransactionSignature
     * @required
     */
    public TransactionSignature $signature;

    /**
     * Indicates what type of transaction this is. Different types have different fields.
     * Valid types, and where their fields are stored:
     * [pay] payment-transaction
     * [keyreg] keyreg-transaction
     * [acfg] asset-config-transaction
     * [axfer] asset-transfer-transaction
     * [afrz] asset-freeze-transaction
     * [appl] application-transaction.
     *
     * @var string
     * @required
     */
    public string $txType;

    /**
     * Free form data.
     * @var string|null
     */
    public ?string $note = null;

    /**
     * (sgnr) this is included with signed transactions when the signing address
     * does not equal the sender. The backend can use this to ensure that auth
     * addr is equal to the accounts auth addr.
     **/
    public ?string $authAddr = null;

    /**
     * When included in a valid transaction, the accounts auth addr will be updated with this value and future
     * signatures must be signed with the key represented by this address.
     **/
    public ?string $rekeyTo = null;

    /**
     * Inner transactions produced by application execution.
     * @var array|PendingTransaction[]
     */
    public array $innerTxns = [];

    /**
     * Fields for a payment transaction.
     * @var PaymentTransaction|null
     */
    public ?PaymentTransaction $paymentTransaction = null;

    /**
     * Fields for application transactions.
     * @var ApplicationTransactionResult|null
     */
    public ?ApplicationTransactionResult $applicationTransaction = null;

    /**
     * Contains more information about the configuration of an asset.
     * @var AssetConfigTransactionResult|null
     */
    public ?AssetConfigTransactionResult $assetConfigTransaction = null;

    /**
     * Fields for an asset freeze transaction.
     * @var AssetFreezeTransactionResult|null
     */
    public ?AssetFreezeTransactionResult $assetFreezeTransaction = null;

    /**
     * Contains more information about the transfer of an asset.
     * @var AssetTransferTransactionResult|null
     */
    public ?AssetTransferTransactionResult $assetTransferTransaction = null;

    /**
     * Fields for a key registration transaction
     * @var KeyRegistrationTransactionResponse|null
     */
    public ?KeyRegistrationTransactionResponse $keyRegistrationTransaction = null;
}
