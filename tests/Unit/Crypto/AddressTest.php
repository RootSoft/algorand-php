<?php

namespace Rootsoft\Algorand\Tests\Unit\Crypto;

use Orchestra\Testbench\TestCase;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Utils\Buffer;
use Rootsoft\Algorand\Utils\CryptoUtils;

class AddressTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testEncodeDecodeStr()
    {
        for ($i = 0; $i < 1000; $i++) {
            $bytes = random_bytes(32);
            $address = Address::fromPublicKey($bytes);
            $address2 = Address::fromAlgorandAddress($address->encodedAddress);
            $this->assertEquals($address, $address2);
        }
    }

    public function testGoldenValues()
    {
        $golden = '7777777777777777777777777777777777777777777777777774MSJUVU';
        $b = Buffer::toBinaryString(CryptoUtils::fillBytes(0xFF));
        $address = Address::fromPublicKey($b);
        $this->assertEquals($address->encodedAddress, $golden);
    }

    public function testAddressForApplication()
    {
        $applicationId = 77;
        $actual = Address::forApplication($applicationId);
        $expected = Address::fromAlgorandAddress('PCYUFPA2ZTOYWTP43MX2MOX2OWAIAXUDNC2WFCXAGMRUZ3DYD6BWFDL5YM');
        $this->assertEquals($expected->encodedAddress, $actual->encodedAddress);
    }
}
