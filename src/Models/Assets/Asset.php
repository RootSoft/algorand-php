<?php

namespace Rootsoft\Algorand\Models\Assets;

/**
 * Specifies both the unique identifier and the parameters for an asset.
 * Class Asset.
 */
class Asset
{
    /**
     * The unique asset identifier.
     * @var int
     * @required
     */
    public int $index;

    /**
     * The asset parameters.
     * @var AssetParams
     * @required
     */
    public AssetParams $params;

    /**
     * Round during which this asset was created.
     * @var int|null
     */
    public ?int $createdAtRound = null;

    /**
     * Whether or not this asset is currently deleted.
     * @var bool|null
     */
    public ?bool $deleted = null;

    /**
     * Round during which this asset was destroyed.
     * @var int|null
     */
    public ?int $destroyedAtRound = null;
}
