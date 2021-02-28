<?php


namespace Rootsoft\Algorand\Models;

/**
 * Describes an asset held by an account.
 * Class AssetHolding
 * @package Rootsoft\Algorand\Models
 */
class AssetHolding
{
    /**
     * Number of units held.
     * @var int
     * @required
     */
    public int $amount;

    /**
     * Asset ID of the holding.
     * @var int
     * @required
     */
    public int $assetId;

    /**
     * Address that created this asset.
     *
     * This is the address where the parameters for this asset can
     * be found, and also the address where unwanted asset units can be sent in the worst case.
     *
     * @var string
     * @required
     */
    public string $creator;

    /**
     * wWether or not the holding is frozen.
     * @var bool
     * @required
     */
    public bool $isFrozen;
}
