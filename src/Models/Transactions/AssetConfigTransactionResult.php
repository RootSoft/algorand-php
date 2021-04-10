<?php


namespace Rootsoft\Algorand\Models\Transactions;

use Rootsoft\Algorand\Models\Assets\AssetParams;

class AssetConfigTransactionResult
{

    /**
     * The id of the asset.
     * @var int
     */
    public int $assetId;

    /**
     * The parameters for the asset config transaction.
     * @var AssetParams|null
     */
    public $params = null;
}
