<?php

namespace Rootsoft\Algorand\Utils\Transformers;

use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\Types\KeyRegistrationTransaction;
use Rootsoft\Algorand\Utils\ArrayUtils;
use Rootsoft\Algorand\Utils\Encoder\EncoderUtils;

class KeyRegistrationTransformer extends BaseTransactionTransformer
{
    public function transform(string $className, array $value): KeyRegistrationTransaction
    {
        $baseTransaction = parent::transform($className, $value);
        $votePK = ArrayUtils::findValueOrNull($value, 'votekey');
        $selectionPK = ArrayUtils::findValueOrNull($value, 'selkey');
        $voteFirst = ArrayUtils::findValueOrNull($value, 'votefst');
        $voteLast = ArrayUtils::findValueOrNull($value, 'votelst');
        $voteKeyDilution = ArrayUtils::findValueOrNull($value, 'votekd');

        return TransactionBuilder::keyRegistration()
            ->append($baseTransaction)
            ->votePublicKey(EncoderUtils::toParticipationPublicKey($votePK))
            ->selectionPublicKey(EncoderUtils::toVRFPublicKey($selectionPK))
            ->voteFirst($voteFirst)
            ->voteLast($voteLast)
            ->voteKeyDilution($voteKeyDilution)
            ->build();
    }

    public function type(): string
    {
        return KeyRegistrationTransaction::class;
    }
}
