<?php

namespace Rootsoft\Algorand\Utils\Transformers;

use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\Types\AssetConfigTransaction;
use Rootsoft\Algorand\Utils\ArrayUtils;
use Rootsoft\Algorand\Utils\Encoder\EncoderUtils;

class AssetConfigTransformer extends BaseTransactionTransformer
{
    public function transform(string $className, array $value): AssetConfigTransaction
    {
        $baseTransaction = parent::transform($className, $value);
        $apar = $value['apar'];
        $assetId = ArrayUtils::findValueOrNull($value, 'caid');
        $total = ArrayUtils::findValueOrNull($apar, 't');
        $decimals = ArrayUtils::findValueOrNull($apar, 'dc');
        $defaultFrozen = ArrayUtils::findValueOrNull($apar, 'df');
        $unitName = ArrayUtils::findValueOrNull($apar, 'un');
        $assetName = ArrayUtils::findValueOrNull($apar, 'an');
        $url = ArrayUtils::findValueOrNull($apar, 'au');
        $metadata = ArrayUtils::findValueOrNull($apar, 'am');
        $managerAddress = ArrayUtils::findValueOrNull($apar, 'm');
        $reserveAddress = ArrayUtils::findValueOrNull($apar, 'r');
        $freezeAddress = ArrayUtils::findValueOrNull($apar, 'f');
        $clawbackAddress = ArrayUtils::findValueOrNull($apar, 'c');

        return TransactionBuilder::assetConfig()
            ->append($baseTransaction)
            ->assetId(EncoderUtils::toBigInteger($assetId))
            ->totalAssetsToCreate(EncoderUtils::toBigInteger($total))
            ->decimals($decimals)
            ->defaultFrozen($defaultFrozen)
            ->unitName($unitName)
            ->assetName($assetName)
            ->url($url)
            ->metadata($metadata)
            ->managerAddress(EncoderUtils::toAddress($managerAddress))
            ->reserveAddress(EncoderUtils::toAddress($reserveAddress))
            ->freezeAddress(EncoderUtils::toAddress($freezeAddress))
            ->clawbackAddress(EncoderUtils::toAddress($clawbackAddress))
            ->build();
    }

    public function type(): string
    {
        return AssetConfigTransaction::class;
    }
}
