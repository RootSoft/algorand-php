<?php

namespace Rootsoft\Algorand\Models\Transactions\Types;

use Brick\Math\BigInteger;
use MessagePack\Type\Bin;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;

class AssetConfigTransaction extends RawTransaction
{
    /**
     * For re-configure or destroy transactions, this is the unique asset ID.
     * On asset creation, the ID is set to zero.
     *
     * @var BigInteger|null
     * @required
     */
    public ?BigInteger $assetId = null;

    /**
     * The total number of base units of the asset to create. This number cannot be changed.
     * Required on creation.
     *
     * @var BigInteger|null
     * @required
     */
    public ?BigInteger $total = null;

    /**
     * The number of digits to use after the decimal point when displaying the asset.
     * If 0, the asset is not divisible.
     * If 1, the base unit of the asset is in tenths.
     * If 2, the base unit of the asset is in hundredths.
     * Required on creation.
     *
     * @var int|null
     * @required
     */
    public ?int $decimals = null;

    /**
     * True to freeze holdings for this asset by default.
     *
     * @var bool|null
     */
    public ?bool $defaultFrozen = null;

    /**
     * The name of a unit of this asset. Supplied on creation. Example: USDT.
     *
     * @var string|null
     */
    public ?string $unitName = null;

    /**
     * The name of the asset. Supplied on creation. Example: Tether.
     *
     * @var string|null
     */
    public ?string $assetName = null;

    /**
     * Specifies a URL where more information about the asset can be retrieved. Max size is 32 bytes.
     *
     * @var string|null
     */
    public ?string $url = null;

    /**
     * This field is intended to be a 32-byte hash of some metadata that is relevant to your asset and/or asset holders.
     * The format of this metadata is up to the application.
     * This field can only be specified upon creation.
     * An example might be the hash of some certificate that acknowledges the digitized asset as the
     * official representation of a particular real-world asset.
     *
     * @var string|null
     */
    public ?string $metaData = null;

    /**
     * The address of the account that can manage the configuration of the asset and destroy it.
     *
     * @var Address|null
     */
    public ?Address $managerAddress = null;

    /**
     * The address of the account that holds the reserve (non-minted) units of the asset.
     * This address has no specific authority in the protocol itself.
     * It is used in the case where you want to signal to holders of your asset that the non-minted units of the
     * asset reside in an account that is different from the default creator account (the sender).
     *
     * @var Address|null
     */
    public ?Address $reserveAddress = null;

    /**
     * The address of the account used to freeze holdings of this asset. If empty, freezing is not permitted.
     *
     * @var Address|null
     */
    public ?Address $freezeAddress = null;

    /**
     * The address of the account that can clawback holdings of this asset. If empty, clawback is not permitted.
     *
     * @var Address|null
     */
    public ?Address $clawbackAddress = null;

    /**
     * Boolean to destroy the asset.
     * use in combination with the asset id.
     *
     * @var bool Destroy the asset if true.
     * @required
     */
    public bool $destroy = false;

    public function toMessagePack(): array
    {
        $fields = parent::toMessagePack();

        // Add the asset id (if needed)
        $fields['caid'] = $this->assetId ? $this->assetId->toInt() : null;

        // Add the asset parameters
        $fields['apar'] = [
            't' => $this->total ? $this->total->toInt() : null,
            'dc' => $this->decimals,
            'df' => $this->defaultFrozen,
            'un' => $this->unitName,
            'an' => $this->assetName,
            'au' => $this->url,
            'am' => new Bin($this->metaData),
            'm' => $this->managerAddress->address ?? null,
            'r' => $this->reserveAddress->address ?? null,
            'f' => $this->freezeAddress->address ?? null,
            'c' => $this->clawbackAddress->address ?? null,
        ];

        // Should the asset be destroyed?
        if ($this->destroy) {
            unset($fields['apar']);
        }

        // Sort the keys
        return $fields;
    }
}
