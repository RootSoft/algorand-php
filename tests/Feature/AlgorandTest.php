<?php


namespace Rootsoft\Algorand\Tests\Feature;

use Orchestra\Testbench\TestCase;
use Rootsoft\Algorand\Mnemonic\Mnemonic;
use Rootsoft\Algorand\Utils\Buffer;
use Rootsoft\Algorand\Utils\CryptoUtils;

class AlgorandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testMnemonic()
    {
        $zeroKeys = Buffer::toBinaryString(CryptoUtils::fillBytes(0));
        $expectedWords = 'abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon invest';
        $seedphrase = Mnemonic::Entropy(bin2hex($zeroKeys));
        $this->assertEquals($seedphrase->words, explode(' ', $expectedWords));

        // Seed
        $seed = Mnemonic::Words($seedphrase->words);
        $this->assertEquals(hex2bin($seed->entropy), $zeroKeys);
    }

}
