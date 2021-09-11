<?php

namespace Rootsoft\Algorand\Utils\Transformers;

use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\Types\ApplicationUpdateTransaction;
use Rootsoft\Algorand\Utils\ArrayUtils;
use Rootsoft\Algorand\Utils\Encoder\EncoderUtils;

class ApplicationUpdateTransformer extends ApplicationTransformer
{
    public function transform(string $className, array $value): ApplicationUpdateTransaction
    {
        $baseTransaction = parent::transform($className, $value);
        $approvalProgram = ArrayUtils::findValueOrNull($value, 'apap');
        $clearStateProgram = ArrayUtils::findValueOrNull($value, 'apsu');

        return TransactionBuilder::applicationUpdate()
            ->append($baseTransaction)
            ->approvalProgram(EncoderUtils::toTEALProgram($approvalProgram))
            ->clearStateProgram(EncoderUtils::toTEALProgram($clearStateProgram))
            ->build();
    }

    public function type(): string
    {
        return ApplicationUpdateTransaction::class;
    }
}
