<?php

namespace Rootsoft\Algorand\Utils\Transformers;

use MessagePack\Packer;
use MessagePack\TypeTransformer\CanPack;
use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Models\Transactions\SignedTransaction;
use Rootsoft\Algorand\Models\Transactions\TransactionType;
use Rootsoft\Algorand\Models\Transactions\Types\ApplicationBaseTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\ApplicationCreateTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\ApplicationUpdateTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\AssetConfigTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\AssetFreezeTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\AssetTransferTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\KeyRegistrationTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\RawPaymentTransaction;
use Rootsoft\Algorand\Utils\ArrayUtils;
use Rootsoft\Algorand\Utils\Encoder\EncoderUtils;

class SignedTransactionTransformer implements CanPack, MessagePackTransformer
{
    private TransformerFactory $factory;

    /**
     * @param TransformerFactory|null $factory
     */
    public function __construct(?TransformerFactory $factory = null)
    {
        $this->factory = $factory ?? new TransformerFactory();
    }

    public function pack(Packer $packer, $value): ?string
    {
        return $value instanceof SignedTransaction
            ? $packer->packMap($value->toMessagePack())
            : null;
    }

    public function transform(string $className, array $value)
    {
        $type = $value['txn']['type'];
        $authAddress = EncoderUtils::toAddress(ArrayUtils::findValueOrNull($value, 'sgnr'));
        $signature = EncoderUtils::toSignature(ArrayUtils::findValueOrNull($value, 'sig'));
        $txn = $value['txn'];

        switch ($type) {
            case TransactionType::PAYMENT():
                $txn = $this->factory->findTransformer(RawPaymentTransaction::class)->transform($className, $txn);

                break;
            case TransactionType::ASSET_CONFIG():
                $txn = $this->factory->findTransformer(AssetConfigTransaction::class)->transform($className, $txn);

                break;
            case TransactionType::ASSET_TRANSFER():
                $txn = $this->factory->findTransformer(AssetTransferTransaction::class)->transform($className, $txn);

                break;
            case TransactionType::ASSET_FREEZE():
                $txn = $this->factory->findTransformer(AssetFreezeTransaction::class)->transform($className, $txn);

                break;
            case TransactionType::KEY_REGISTRATION():
                $txn = $this->factory->findTransformer(KeyRegistrationTransaction::class)->transform($className, $txn);

                break;
            case TransactionType::APPLICATION_CALL():
                // Check if create, update or base
                if ($this->isApplicationCreateTransaction($txn)) {
                    $txn = $this->factory->findTransformer(ApplicationCreateTransaction::class)->transform($className, $txn);
                } elseif ($this->isApplicationUpdateTransaction($txn)) {
                    $txn = $this->factory->findTransformer(ApplicationUpdateTransaction::class)->transform($className, $txn);
                } else {
                    $txn = $this->factory->findTransformer(ApplicationBaseTransaction::class)->transform($className, $txn);
                }

                break;
        }

        // Logic Signature
        $lsig = null;
        if (array_key_exists('lsig', $value)) {
            $lsig = $this->factory->findTransformer(LogicSignature::class)->transform($className, $value['lsig']);
        }

        // TODO Msig Signature

        $signedTx = new SignedTransaction($txn, $signature, $lsig);
        $signedTx->setAuthAddr($authAddress);

        return $signedTx;
    }

    public function type(): string
    {
        return SignedTransaction::class;
    }

    /**
     * Check if the transaction is an application create.
     * TODO Rework to one ApplicationTransaction.
     *
     * @param array $values
     * @return bool
     */
    private function isApplicationCreateTransaction(array $values) : bool
    {
        return array_key_exists('apls', $values) || array_key_exists('apgs', $values) || array_key_exists('apep', $values);
    }

    /**
     * Check if the transaction is an application update.
     * TODO Rework to one ApplicationTransaction.
     *
     * @param array $values
     * @return bool
     */
    private function isApplicationUpdateTransaction(array $values) : bool
    {
        return array_key_exists('apap', $values) || array_key_exists('apsu', $values);
    }
}
