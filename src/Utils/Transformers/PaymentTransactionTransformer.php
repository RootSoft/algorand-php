<?php

namespace Rootsoft\Algorand\Utils\Transformers;

use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\Types\RawPaymentTransaction;
use Rootsoft\Algorand\Utils\ArrayUtils;

class PaymentTransactionTransformer implements MessagePackTransformer
{

    public function transform(string $className, array $value): RawPaymentTransaction
    {
        $amount = ArrayUtils::findValueOrNull($value, 'amt');
        $receiver = ArrayUtils::findValueOrNull($value, 'rcv');
        $closeRemainderTo = ArrayUtils::findValueOrNull($value, 'close');

        // Build the base transaction
        $baseTransactionBuilder = new BaseTransactionTransformer();
        $baseTransaction = $baseTransactionBuilder->transform($className, $value);

        return TransactionBuilder::payment()
            ->append($baseTransaction)
            ->amount($amount)
            ->receiver($receiver ? Address::fromPublicKey($receiver) : null)
            ->closeRemainderTo($closeRemainderTo ? Address::fromPublicKey($closeRemainderTo) : null)
            ->build();
    }

    public function type(): string
    {
        return RawPaymentTransaction::class;
    }

}
