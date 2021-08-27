<?php


namespace Rootsoft\Algorand\Models\Transactions;

/**
 * Contains all fields common to all transactions and serves as an envelope to all transactions type.
 * TODO Add other fields
 * https://developer.algorand.org/docs/reference/rest-apis/indexer/#transaction
 *
 * Class Transaction
 * @package Rootsoft\Algorand\Models\Transactions
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
     * Transaction fee.
     * @var int
     * @required
     */
    public int $fee;

    /**
     * First valid round for this transaction
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
     * Transaction ID
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
     * Free form data.
     * @var string|null
     */
    public ?string $note = null;

    /**
     * Fields for a payment transaction.
     * @var PaymentTransaction|null
     */
    public ?PaymentTransaction $paymentTransaction = null;

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
     * [appl] application-transaction
     *
     * @var string
     * @required
     */
    public string $txType;

    /**
     * The index of the created asset.
     * @var int|null
     */
    public ?int $createdAssetIndex = null;

    /**
     * Contains more information about the configuration of an asset.
     * @var AssetConfigTransactionResult|null
     */
    public ?AssetConfigTransactionResult $assetConfigTransaction = null;

    /**
     * Contains more information about the transfer of an asset.
     * @var AssetTransferTransactionResult|null
     */
    public ?AssetTransferTransactionResult $assetTransferTransaction = null;
}
