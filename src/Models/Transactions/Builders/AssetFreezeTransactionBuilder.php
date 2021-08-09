<?php


namespace Rootsoft\Algorand\Models\Transactions\Builders;

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\Types\AssetFreezeTransaction;
use Rootsoft\Algorand\Models\Transactions\TransactionType;

class AssetFreezeTransactionBuilder extends RawTransactionBuilder
{
    protected AssetFreezeTransaction $assetTransaction;

    /**
     * AssetTransferTransactionBuilder constructor.
     */
    public function __construct()
    {
        $this->assetTransaction = new AssetFreezeTransaction();
        parent::__construct(TransactionType::ASSET_FREEZE(), $this->assetTransaction);
    }

    /**
     * The ID of the asset being frozen or unfrozen.
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
     * The address of the account whose asset is being frozen or unfrozen.
     *
     * @param Address $address
     * @return $this
     */
    public function freezeTarget(Address $address)
    {
        $this->assetTransaction->freezeAddress = $address;

        return $this;
    }

    /**
     * True to freeze the asset.
     *
     * @param bool $freeze
     * @return $this
     */
    public function freeze(bool $freeze)
    {
        $this->assetTransaction->freeze = $freeze;

        return $this;
    }

    /**
     * @return AssetFreezeTransaction
     * @throws AlgorandException
     */
    public function build()
    {
        parent::build();

        return $this->assetTransaction;
    }
}
