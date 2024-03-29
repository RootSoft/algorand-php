<?php

namespace Rootsoft\Algorand\Models\Assets;

/**
 * A single asset result.
 * Class AssetResult.
 */
class AssetResult
{
    /**
     * Round at which the results were computed.
     * @var int
     * @required
     */
    public int $currentRound = 0;

    /**
     * The asset.
     * @var Asset
     * @required
     */
    public Asset $asset;
}
