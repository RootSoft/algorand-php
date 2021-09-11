<?php

namespace Rootsoft\Algorand\Utils\Transformers;

use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;
use Rootsoft\Algorand\Utils\ArrayUtils;

class BaseTransactionTransformer implements MessagePackTransformer
{

    public function transform(string $className, array $value): RawTransaction
    {
        $sender = ArrayUtils::findValueOrNull($value, 'snd');
        $rekeyTo = ArrayUtils::findValueOrNull($value, 'rekey');

        return TransactionBuilder::raw()
            ->type(ArrayUtils::findValueOrNull($value, 'type'))
            ->flatFee(ArrayUtils::findValueOrNull($value, 'fee'))
            ->firstValid(ArrayUtils::findValueOrNull($value, 'fv'))
            ->lastValid(ArrayUtils::findValueOrNull($value, 'lv'))
            ->note(ArrayUtils::findValueOrNull($value, 'note'))
            ->sender($sender != null ? Address::fromPublicKey($sender) : null)
            ->genesisId(ArrayUtils::findValueOrNull($value, 'gen'))
            ->genesisHash(ArrayUtils::findValueOrNull($value, 'gh'))
            ->lease(ArrayUtils::findValueOrNull($value, 'lx'))
            ->group(ArrayUtils::findValueOrNull($value, 'grp'))
            ->rekeyTo($rekeyTo != null ? Address::fromPublicKey($rekeyTo) : null)
            ->build();
    }

    public function type(): string
    {
        return RawTransaction::class;
    }

}
