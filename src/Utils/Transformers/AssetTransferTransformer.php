<?php

namespace Rootsoft\Algorand\Utils\Transformers;

use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\Types\AssetTransferTransaction;
use Rootsoft\Algorand\Utils\ArrayUtils;
use Rootsoft\Algorand\Utils\Encoder\EncoderUtils;

class AssetTransferTransformer extends BaseTransactionTransformer
{
    public function transform(string $className, array $value): AssetTransferTransaction
    {
        $baseTransaction = parent::transform($className, $value);
        $assetId = ArrayUtils::findValueOrNull($value, 'xaid');
        $amount = ArrayUtils::findValueOrNull($value, 'aamt');
        $assetSender = ArrayUtils::findValueOrNull($value, 'asnd');
        $assetReceiver = ArrayUtils::findValueOrNull($value, 'arcv');
        $assetCloseTo = ArrayUtils::findValueOrNull($value, 'aclose');

        return TransactionBuilder::assetTransfer()
            ->append($baseTransaction)
            ->assetId(EncoderUtils::toBigInteger($assetId))
            ->amount($amount)
            ->assetSender(EncoderUtils::toAddress($assetSender))
            ->assetReceiver(EncoderUtils::toAddress($assetReceiver))
            ->assetCloseTo(EncoderUtils::toAddress($assetCloseTo))
            ->build();
    }

    public function type(): string
    {
        return AssetTransferTransaction::class;
    }
}
