<?php

namespace Rootsoft\Algorand\Tests\Unit\Accounts;

use Brick\Math\BigInteger;
use Brick\Math\Exception\NumberFormatException;
use Orchestra\Testbench\TestCase;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Utils\Encoder\BigIntEncoder;

class EncoderTest extends TestCase
{
    private BigInteger $MAX_UINT64;

    protected function setUp(): void
    {
        parent::setUp();
        $this->MAX_UINT64 = BigIntEncoder::getInstance()->MAX_UINT64;
    }

    public function testEncodeUint64()
    {
        $inputs = [
            BigInteger::of(0),
            BigInteger::of(1),
            BigInteger::of(500),
            $this->MAX_UINT64->minus(BigInteger::one()),
            $this->MAX_UINT64,
        ];

        $expectedItems = [
            [0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 1],
            [0, 0, 0, 0, 0, 0, 1, 0xf4],
            [0xff, 0xff, 0xff, 0xff, 0xff, 0xff, 0xff, 0xfe],
            [0xff, 0xff, 0xff, 0xff, 0xff, 0xff, 0xff, 0xff],
        ];

        for ($i = 0; $i < count($inputs); $i++) {
            $input = $inputs[$i];
            $expected = $expectedItems[$i];

            $actual = BigIntEncoder::getInstance()->encodeUint64($input);
            $this->assertEquals($expected, $actual);
        }
    }

    public function testEncodeUint64Exception()
    {
        // Test invalid inputs
        $invalidInputs = [
            BigInteger::of(-1),
            $this->MAX_UINT64->plus(BigInteger::one()),
        ];

        for ($i = 0; $i < count($invalidInputs); $i++) {
            $input = $invalidInputs[$i];
            $this->expectException(AlgorandException::class);
            BigIntEncoder::getInstance()->encodeUint64($input);
        }
    }

    public function testDecodeUint64()
    {
        $inputs = [
            [0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 1],
            [0, 0, 0, 0, 0, 0, 1, 0xf4],
            [0xff, 0xff, 0xff, 0xff, 0xff, 0xff, 0xff, 0xfe],
            [0xff, 0xff, 0xff, 0xff, 0xff, 0xff, 0xff, 0xff],
        ];

        $expectedItems = [
            BigInteger::of(0),
            BigInteger::of(1),
            BigInteger::of(500),
            $this->MAX_UINT64->minus(BigInteger::one()),
            $this->MAX_UINT64,
        ];

        for ($i = 0; $i < count($inputs); $i++) {
            $input = $inputs[$i];
            $expected = $expectedItems[$i];

            $actual = BigIntEncoder::getInstance()->decodeUint64($input);
            $this->assertEquals($expected, $actual);
        }
    }

    public function testDecodeUint64Exception()
    {
        // Test invalid inputs
        $invalidInputs = [
            [],
            [0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 0, 0],
        ];

        for ($i = 0; $i < count($invalidInputs); $i++) {
            $input = $invalidInputs[$i];
            $this->expectException(NumberFormatException::class);
            BigIntEncoder::getInstance()->decodeUint64($input);
        }
    }

}
