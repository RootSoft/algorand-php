<?php


namespace Rootsoft\Algorand\Utils\Transformers;

use MessagePack\Packer;
use MessagePack\TypeTransformer\CanPack;
use Rootsoft\Algorand\Models\Transactions\SignedTransaction;

class SignedTransactionTransformer implements CanPack {
    public function pack(Packer $packer, $value): ?string {
        return $value instanceof SignedTransaction
            ? $packer->packMap($value->toArray())
            : null;
    }
}
