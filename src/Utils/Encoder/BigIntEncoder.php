<?php

namespace Rootsoft\Algorand\Utils\Encoder;

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Utils\Buffer;
use Rootsoft\Algorand\Utils\CryptoUtils;

class BigIntEncoder
{
    const UINT64_LENGTH = 8;

    public BigInteger $MAX_UINT64;

    /**
     * A singleton instance, handling the encoding.
     * @var
     */
    private static $instance;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->MAX_UINT64 = BigInteger::fromBase('FFFFFFFFFFFFFFFF', 16);
        }

        return self::$instance;
    }

    /**
     * @throws AlgorandException
     */
    public function encodeUint64($value): array
    {
        $value = $value instanceof BigInteger ? $value : BigInteger::of($value);
        if ($value->isLessThan(0) || $value->isGreaterThan($this->MAX_UINT64)) {
            throw new AlgorandException('Value cannot be represented by a uint64');
        }

        $fixedLengthEncoding = CryptoUtils::fillBytes(0, 8);
        $encodedValue = Buffer::toArray($value->toBytes());

        if (!empty($encodedValue) && $encodedValue[0] == 0) {
            // encodedValue is actually encoded as a signed 2's complement value,
            // so there may be a leading 0 for some encodings -- ignore it
            $encodedValue = array_slice($encodedValue, 1, count($encodedValue));
        }

        $start = self::UINT64_LENGTH - count($encodedValue);
        $end = $start + count($encodedValue);

        array_splice($fixedLengthEncoding, $start, count($fixedLengthEncoding) - $start, $encodedValue);

        return $fixedLengthEncoding;
    }

    public function decodeUint64($data): BigInteger
    {
        $data = is_array($data) ? Buffer::toBinaryString($data) : $data;
        return BigInteger::fromBytes($data, false);
    }
}