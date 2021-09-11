<?php

namespace Rootsoft\Algorand\Utils\Transformers;

use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\Types\AssetFreezeTransaction;
use Rootsoft\Algorand\Utils\ArrayUtils;
use Rootsoft\Algorand\Utils\Encoder\EncoderUtils;

class AssetFreezeTransformer extends BaseTransactionTransformer
{
    public function transform(string $className, array $value): AssetFreezeTransaction
    {
        $baseTransaction = parent::transform($className, $value);
        $freezeAddress = ArrayUtils::findValueOrNull($value, 'fadd');
        $assetId = ArrayUtils::findValueOrNull($value, 'faid');
        $freeze = ArrayUtils::findValueOrNull($value, 'afrz');

        return TransactionBuilder::assetFreeze()
            ->append($baseTransaction)
            ->assetId(EncoderUtils::toBigInteger($assetId))
            ->freezeTarget(EncoderUtils::toAddress($freezeAddress))
            ->freeze($freeze)
            ->build();
    }

    public function type(): string
    {
        return AssetFreezeTransaction::class;
    }
}
