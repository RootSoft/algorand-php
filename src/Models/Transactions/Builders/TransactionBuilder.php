<?php


namespace Rootsoft\Algorand\Models\Transactions\Builders;

use Rootsoft\Algorand\Models\Applications\OnCompletion;

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
     * @return ApplicationUpdateTransactionBuilder
     */
    public static function applicationUpdate()
    {
        return new ApplicationUpdateTransactionBuilder(null);
    }

    /**
     *
     * @return ApplicationBaseTransactionBuilder
     */
    public static function applicationOptIn()
    {
        return new ApplicationBaseTransactionBuilder(null, OnCompletion::OPT_IN_OC());
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
     * @return ApplicationBaseTransactionBuilder
     */
    public static function applicationClearState()
    {
        return new ApplicationBaseTransactionBuilder(null, OnCompletion::CLEAR_STATE_OC());
    }

    /**
     *
     * @return ApplicationBaseTransactionBuilder
     */
    public static function applicationCloseOut()
    {
        return new ApplicationBaseTransactionBuilder(null, OnCompletion::CLOSE_OUT_OC());
    }

    /**
     *
     * @return ApplicationBaseTransactionBuilder
     */
    public static function applicationDelete()
    {
        return new ApplicationBaseTransactionBuilder(null, OnCompletion::DELETE_APPLICATION_OC());
    }
}
