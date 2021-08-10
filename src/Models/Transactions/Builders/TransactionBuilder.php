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

    /**
     *
     * @return ApplicationCreateTransactionBuilder
     */
    public static function applicationCreate()
    {
        return new ApplicationCreateTransactionBuilder();
    }

    /**
     *
     * @return ApplicationBaseTransactionBuilder
     */
    public static function applicationCall()
    {
        return new ApplicationBaseTransactionBuilder();
    }

    /**
     *
     * @return ApplicationUpdateTransactionBuilder
     */
    public static function applicationUpdate()
    {
        return new ApplicationUpdateTransactionBuilder();
    }
}
