<?php

namespace Rootsoft\Algorand\Models\Transactions;

use Rootsoft\Algorand\Models\Assets\AssetParams;

class AssetConfigTransactionResult
{
    /**
     * The id of the asset.
     * @var int|null
     */
    public ?int $assetId = null;

    /**
     * The parameters for the asset config transaction.
     * @var AssetParams|null
     */
    public ?AssetParams $params = null;
}
