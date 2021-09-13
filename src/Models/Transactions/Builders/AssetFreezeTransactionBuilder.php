<?php

namespace Rootsoft\Algorand\Models\Transactions\Builders;

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\TransactionType;
use Rootsoft\Algorand\Models\Transactions\Types\AssetFreezeTransaction;

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
     * @param BigInteger|null $assetId
     * @return $this
     */
    public function assetId(?BigInteger $assetId): self
    {
        $this->assetTransaction->assetId = $assetId;

        return $this;
    }

    /**
     * The address of the account whose asset is being frozen or unfrozen.
     *
     * @param Address|null $address
     * @return $this
     */
    public function freezeTarget(?Address $address): self
    {
        $this->assetTransaction->freezeAddress = $address;

        return $this;
    }

    /**
     * True to freeze the asset.
     *
     * @param bool|null $freeze
     * @return $this
     */
    public function freeze(?bool $freeze): self
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
