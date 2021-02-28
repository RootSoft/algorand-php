<?php


namespace Rootsoft\Algorand\Models;

/**
 * A simplified version of AssetHolding
 *
 * Class MiniAssetHolding
 * @package Rootsoft\Algorand\Models
 */
class MiniAssetHolding
{

    /**
     * @var string
     * @required
     */
    public string $address;

    /**
     * @var int
     * @required
     */
    public int $amount;

    /**
     * @var bool|null
     */
    public ?bool $deleted;

    /**
     * @var bool
     * @required
     */
    public bool $isFrozen;

    /**
     * @var int|null
     */
    public ?int $optedInAtRound;

    /**
     * @var int|null
     */
    public ?int $optedOutAtRound;
}
