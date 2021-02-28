<?php


namespace Rootsoft\Algorand\Models\Transactions\Builders;

use Rootsoft\Algorand\Models\Transactions\TransactionType;

class TransactionBuilder extends RawTransactionBuilder
{

    /**
     *
     * @return PaymentTransactionBuilder
     */
    public static function payment()
    {

        return new PaymentTransactionBuilder();
    }

    /**
     *
     * @return AssetConfigTransactionBuilder
     */
    public static function assetConfig()
    {
        return new AssetConfigTransactionBuilder();
    }

    /**
     *
     * @return AssetTransferTransactionBuilder
     */
    public static function assetTransfer()
    {
        return new AssetTransferTransactionBuilder();
    }

    /**
     *
     * @return AssetFreezeTransactionBuilder
     */
    public static function assetFreeze()
    {
        return new AssetFreezeTransactionBuilder();
    }
}
