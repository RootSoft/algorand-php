<?php


namespace Rootsoft\Algorand\Models\Transactions\Builders;

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

    /**
     *
     * @return KeyRegistrationTransactionBuilder
     */
    public static function keyRegistration()
    {
        return new KeyRegistrationTransactionBuilder();
    }
}
