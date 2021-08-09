<?php


namespace Rootsoft\Algorand\Models\Transactions\Types;

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;

/**
 * Freezing or unfreezing an asset for an account requires a transaction that is signed by the freeze account.
 *
 * Class AssetFreezeTransaction
 * @package Rootsoft\Algorand\Models\Transactions\Types
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

    public function toMessagePack(): array
    {
        $fields = parent::toMessagePack();

        $fields['fadd'] = $this->freezeAddress->address ?? null;
        $fields['faid'] = $this->assetId ? $this->assetId->toInt() : null;
        $fields['afrz'] = $this->freeze;

        return $fields;
    }
}
