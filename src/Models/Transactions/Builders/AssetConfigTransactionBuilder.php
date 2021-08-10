<?php


namespace Rootsoft\Algorand\Models\Transactions\Builders;

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\TransactionType;
use Rootsoft\Algorand\Models\Transactions\Types\AssetConfigTransaction;

class AssetConfigTransactionBuilder extends RawTransactionBuilder
{
    protected AssetConfigTransaction $assetTransaction;

    /**
     * AssetTransactionBuilder constructor.
     */
    public function __construct()
    {
        $this->assetTransaction = new AssetConfigTransaction();
        parent::__construct(TransactionType::ASSET_CONFIG(), $this->assetTransaction);
    }

    /**
     * The unique id of the asset.
     *
     * For re-configure or destroy transactions, this is the unique asset ID.
     * On asset creation, the ID is set to zero.
     *
     * @param BigInteger $assetId
     * @return $this
     */
    public function assetId(BigInteger $assetId)
    {
        $this->assetTransaction->assetId = $assetId;

        return $this;
    }

    /**
     * The total number of base units of the asset to create. This number cannot be changed.
     *
     * @param BigInteger $total
     * @required on creation
     * @return $this
     */
    public function totalAssetsToCreate(BigInteger $total)
    {
        $this->assetTransaction->total = $total;

        return $this;
    }

    /**
     * The number of digits to use after the decimal point when displaying the asset.
     * If 0, the asset is not divisible.
     * If 1, the base unit of the asset is in tenths.
     * If 2, the base unit of the asset is in hundredths.
     *
     * @param int $decimals
     * @required on creation
     * @return $this
     */
    public function decimals(int $decimals)
    {
        $this->assetTransaction->decimals = $decimals;

        return $this;
    }

    /**
     * True to freeze holdings for this asset by default.
     *
     * @param bool $defaultFrozen
     * @return $this
     */
    public function defaultFrozen(bool $defaultFrozen)
    {
        $this->assetTransaction->defaultFrozen = $defaultFrozen;

        return $this;
    }

    /**
     * The name of a unit of this asset. Supplied on creation. Example: USDT
     *
     * @param string
     * @return $this
     */
    public function unitName(string $unitName)
    {
        $this->assetTransaction->unitName = $unitName;

        return $this;
    }

    /**
     * The name of the asset. Supplied on creation. Example: Tether
     *
     * @param string $assetName
     * @return $this
     */
    public function assetName(string $assetName)
    {
        $this->assetTransaction->assetName = $assetName;

        return $this;
    }

    /**
     * Specifies a URL where more information about the asset can be retrieved. Max size is 32 bytes.
     *
     * @param string $url
     * @return $this
     */
    public function url(string $url)
    {
        $this->assetTransaction->url = $url;

        return $this;
    }

    /**
     * This field is intended to be a 32-byte hash of some metadata that is relevant to your asset and/or asset holders.
     * The format of this metadata is up to the application.
     * This field can only be specified upon creation.
     *
     * An example might be the hash of some certificate that acknowledges the digitized asset as the official representation of a particular real-world asset.
     *
     * @param string $metaData
     * @return $this
     */
    public function metaDataHash(string $metaData)
    {
        $this->assetTransaction->metaDataHash = $metaData;

        return $this;
    }

    /**
     * The address of the account that can manage the configuration of the asset and destroy it.
     *
     * @param Address|null $address
     * @return $this
     */
    public function managerAddress(?Address $address)
    {
        $this->assetTransaction->managerAddress = $address;

        return $this;
    }

    /**
     * The address of the account that holds the reserve (non-minted) units of the asset.
     * This address has no specific authority in the protocol itself.
     *
     * It is used in the case where you want to signal to holders of your asset that the non-minted units of the asset
     * reside in an account that is different from the default creator account (the sender).
     *
     * @param Address|null $address
     * @return $this
     */
    public function reserveAddress(?Address $address)
    {
        $this->assetTransaction->reserveAddress = $address;

        return $this;
    }

    /**
     * The address of the account used to freeze holdings of this asset.
     * If empty, freezing is not permitted.
     *
     * @param Address|null $address
     * @return $this
     */
    public function freezeAddress(?Address $address)
    {
        $this->assetTransaction->freezeAddress = $address;

        return $this;
    }

    /**
     * The address of the account that can clawback holdings of this asset.
     * If empty, clawback is not permitted.
     *
     * @param Address|null $address
     * @return $this
     */
    public function clawbackAddress(?Address $address)
    {
        $this->assetTransaction->clawbackAddress = $address;

        return $this;
    }

    /**
     * Remove an asset from the Algorand ledger.
     * To destroy an existing asset on Algorand, the original creator must be in possession of all units of the asset
     * and the manager must send and therefore authorize the transaction.
     *
     * @return AssetConfigTransaction
     */
    public function destroy()
    {
        $this->assetTransaction->destroy = true;

        return $this->assetTransaction;
    }

    /**
     * @return AssetConfigTransaction
     * @throws AlgorandException
     */
    public function build()
    {
        parent::build();
        // TODO total and decimals are required on creation!
        return $this->assetTransaction;
    }

    public function estimateTransactionSize(): int
    {
        // TODO: Implement estimateTransactionSize() method.
        return 0;
    }
}
