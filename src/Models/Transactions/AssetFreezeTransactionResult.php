<?php

namespace Rootsoft\Algorand\Models\Transactions;

class AssetFreezeTransactionResult
{
    /**
     * Address of the account whose asset is being frozen or thawed
     * @var string|null
     */
    public ?string $address = null;

    /**
     * ID of the asset being frozen or thawed.
     * @var int
     */
    public int $assetId = 0;

    /**
     * The new freeze status.
     * @var bool
     */
    public bool $newFreezeStatus = false;
}
