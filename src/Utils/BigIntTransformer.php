<?php


namespace Rootsoft\Algorand\Utils;

use Brick\Math\BigInteger;
use MessagePack\Packer;
use MessagePack\TypeTransformer\CanPack;

class BigIntTransformer implements CanPack
{
    public function pack(Packer $packer, $value): ?string
    {
        if (! ($value instanceof BigInteger)) {
            return null;
        }

        //$packer->packStr($value->)
    }
}
