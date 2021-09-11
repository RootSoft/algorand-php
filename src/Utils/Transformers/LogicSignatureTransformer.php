<?php

namespace Rootsoft\Algorand\Utils\Transformers;

use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Utils\ArrayUtils;
use Rootsoft\Algorand\Utils\Encoder\EncoderUtils;

class LogicSignatureTransformer implements MessagePackTransformer
{
    public function transform(string $className, array $value): LogicSignature
    {
        $logic = ArrayUtils::findValueOrNull($value, 'l');
        $arguments = ArrayUtils::findValueOrNull($value, 'arg');
        $sig = EncoderUtils::toSignature(ArrayUtils::findValueOrNull($value, 'sig'));

        return new LogicSignature($logic, $arguments, $sig, null);
    }

    public function type(): string
    {
        return LogicSignature::class;
    }
}
