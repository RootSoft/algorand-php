<?php

namespace Rootsoft\Algorand\Models\Assets;

/**
 * Specifies the parameters for an asset.
 * [apar] when part of an AssetConfig transaction.
 *
 * Class AssetParams
 */
class AssetParams
{
    /**
     * Address of account used to clawback holdings of this asset.
     *
     * If empty, clawback is not permitted.
     * @var string|null
     */
    public string $clawback;

    /**
     * The address that created this asset.
     *
     * This is the address where the parameters for this asset can be found,
     * and also the address where unwanted asset units can be sent in the worst case.
     * @var string
     * @required
     */
    public string $creator;

    /**
     * The number of digits to use after the decimal point when displaying this asset.
     *
     * If 0, the asset is not divisible.
     * If 1, the base unit of the asset is in tenths.
     * If 2, the base unit of the asset is in hundredths, and so on.
     *
     * This value must be between 0 and 19 (inclusive).
     * Minimum value : 0
     * Maximum value : 19
     * @var int
     * @required
     */
    public int $decimals;

    /**
     * Whether holdings of this asset are frozen by default.
     * @var bool|null
     */
    public bool $defaultFrozen;

    /**
     * Address of account used to freeze holdings of this asset.
     *
     * If empty, freezing is not permitted.
     * @var string|null
     */
    public string $freeze;

    /**
     * Address of account used to manage the keys of this asset and to destroy it.
     *
     * @var string|null
     */
    public string $manager;

    /**
     * A commitment to some unspecified asset metadata. The format of this metadata is up to the application.
     *
     * @var string|null
     */
    public string $metadataHash;

    /**
     * Name of this asset, as supplied by the creator.
     *
     * @var string|null
     */
    public string $name;

    /**
     * Address of account holding reserve (non-minted) units of this asset.
     *
     * @var string|null
     */
    public string $reserve;

    /**
     * The total number of units of this asset.
     *
     * @var int
     * @required
     */
    public int $total;

    /**
     * Name of a unit of this asset, as supplied by the creator.
     *
     * @var string|null
     */
    public string $unitName;

    /**
     * URL where more information about the asset can be retrieved.
     *
     * @var string|null
     */
    public string $url;
}
