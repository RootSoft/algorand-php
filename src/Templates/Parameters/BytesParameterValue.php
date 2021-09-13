<?php

namespace Rootsoft\Algorand\Templates\Parameters;

use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Crypto\Logic;
use Rootsoft\Algorand\Utils\Buffer;

class BytesParameterValue extends ParameterValue
{
    public function __construct(int $offset, string $value)
    {
        parent::__construct($offset, self::convertToBytes($value));
    }

    public static function fromBase64(int $offset, string $source): self
    {
        $buffer = Base64::decode($source);

        return new self($offset, $buffer);
    }

    public function placeholderSize(): int
    {
        return 2;
    }

    private static function convertToBytes(string $value): array
    {
        $len = Logic::putUVarint(strlen($value));

        return array_merge($len, Buffer::toArray($value));
    }
}
