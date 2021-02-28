<?php


namespace Rootsoft\Algorand\Models\Transactions\Assets;

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;
use Rootsoft\Algorand\Utils\AlgorandUtils;

/**
 * Freezing or unfreezing an asset for an account requires a transaction that is signed by the freeze account.
 *
 * Class AssetFreezeTransaction
 * @package Rootsoft\Algorand\Models\Transactions\Assets
 */
class AssetFreezeTransaction extends RawTransaction
{

    /**
     * The address of the account whose asset is being frozen or unfrozen.
     *
     * @var Address|null
     * @required
     */
    public ?Address $freezeAddress = null;

    /**
     * The ID of the asset being frozen or unfrozen.
     *
     * @var BigInteger|null
     * @required
     */
    public ?BigInteger $assetId = null;

    /**
     * True to freeze the asset.
     *
     * @var bool|null
     */
    public ?bool $freeze = null;

    public function toArray()
    {
        $transaction = parent::toArray();

        $transaction['fadd'] = $this->freezeAddress->address ?? null;
        $transaction['faid'] = $this->assetId ? $this->assetId->toInt() : null;
        $transaction['afrz'] = $this->freeze;

        return AlgorandUtils::algorand_array_clean($transaction);
    }
}
