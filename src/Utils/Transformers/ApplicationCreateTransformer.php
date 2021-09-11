<?php

namespace Rootsoft\Algorand\Utils\Transformers;

use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\Types\ApplicationCreateTransaction;
use Rootsoft\Algorand\Utils\ArrayUtils;
use Rootsoft\Algorand\Utils\Encoder\EncoderUtils;

class ApplicationCreateTransformer extends ApplicationUpdateTransformer
{
    public function transform(string $className, array $value): ApplicationCreateTransaction
    {
        $baseTransaction = parent::transform($className, $value);
        $localStateSchema = ArrayUtils::findValueOrNull($value, 'apls');
        $globalStateSchema = ArrayUtils::findValueOrNull($value, 'apgs');
        $extraPages = ArrayUtils::findValueOrNull($value, 'apep');

        return TransactionBuilder::applicationCreate()
            ->append($baseTransaction)
            ->localStateSchema(EncoderUtils::toStateSchema($localStateSchema))
            ->globalStateSchema(EncoderUtils::toStateSchema($globalStateSchema))
            ->extraPages($extraPages)
            ->build();
    }

    public function type(): string
    {
        return ApplicationCreateTransaction::class;
    }
}
