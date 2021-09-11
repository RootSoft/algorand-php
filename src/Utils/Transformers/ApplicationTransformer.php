<?php

namespace Rootsoft\Algorand\Utils\Transformers;

use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Applications\OnCompletion;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\Types\ApplicationBaseTransaction;
use Rootsoft\Algorand\Utils\ArrayUtils;
use Rootsoft\Algorand\Utils\Encoder\EncoderUtils;

class ApplicationTransformer extends BaseTransactionTransformer
{
    public function transform(string $className, array $value): ApplicationBaseTransaction
    {
        $baseTransaction = parent::transform($className, $value);
        $applicationId = ArrayUtils::findValueOrNull($value, 'apid');
        $onCompletion = OnCompletion::from(ArrayUtils::findValueOrNull($value, 'apan') ?? 0);
        $arguments = ArrayUtils::findValueOrNull($value, 'apaa');
        $accounts = ArrayUtils::findValueOrNull($value, 'apat') ?? [];
        $foreignApps = ArrayUtils::findValueOrNull($value, 'apfa');
        $foreignAssets = ArrayUtils::findValueOrNull($value, 'apas');

        return TransactionBuilder::applicationBase()
            ->append($baseTransaction)
            ->applicationId(EncoderUtils::toBigInteger($applicationId))
            ->onCompletion($onCompletion)
            ->arguments($arguments)
            ->accounts(array_map(fn (string $address) => Address::fromPublicKey($address), $accounts))
            ->foreignApps($foreignApps)
            ->foreignAssets($foreignAssets)
            ->build();
    }

    public function type(): string
    {
        return ApplicationBaseTransaction::class;
    }
}
