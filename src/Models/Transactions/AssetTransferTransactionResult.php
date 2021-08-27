<?php


namespace Rootsoft\Algorand\Models\Transactions;

class AssetTransferTransactionResult
{

    /**
     * Amount of asset to transfer.
     * @var int
     * @required
     */
    public int $amount;

    /**
     * ID of the asset being transferred.
     * @var int
     */
    public int $assetId;

    /**
     * Number of assets transfered to the close-to account as part of the transaction.
     * @var int|null
     */
    public ?int $closeAmount = null;

    /**
     * Recipient address of the transfer.
     * @var string
     * @required
     */
    public string $receiver;

    /**
     * The effective sender during a clawback transactions.
     * @var string
     */
    public ?string $sender = null;
}
