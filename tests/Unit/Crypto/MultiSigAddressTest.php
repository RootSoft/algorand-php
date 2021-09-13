<?php

namespace Rootsoft\Algorand\Tests\Unit\Crypto;

use Orchestra\Testbench\TestCase;
use Rootsoft\Algorand\Crypto\Ed25519PublicKey;
use Rootsoft\Algorand\Crypto\MultiSignatureAddress;
use Rootsoft\Algorand\Models\Accounts\Address;

class MultiSigAddressTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testMultiSigAddressToString()
    {
        $one = Address::fromAlgorandAddress('XMHLMNAVJIMAW2RHJXLXKKK4G3J3U6VONNO3BTAQYVDC3MHTGDP3J5OCRU');
        $two = Address::fromAlgorandAddress('HTNOX33OCQI2JCOLZ2IRM3BC2WZ6JUILSLEORBPFI6W7GU5Q4ZW6LINHLA');
        $three = Address::fromAlgorandAddress('E6JSNTY4PVCY3IRZ6XEDHEO6VIHCQ5KGXCIQKFQCMB2N6HXRY4IB43VSHI');

        $publicKeys = array_map(fn (Address $value) => new Ed25519PublicKey($value->address), [$one, $two, $three]);
        $msigAddr = new MultiSignatureAddress(1, 2, $publicKeys);

        $this->assertEquals($msigAddr->toString(), 'UCE2U2JC4O4ZR6W763GUQCG57HQCDZEUJY4J5I6VYY4HQZUJDF7AKZO5GM');
    }
}
