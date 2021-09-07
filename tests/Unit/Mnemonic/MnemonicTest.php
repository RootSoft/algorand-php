<?php


namespace Rootsoft\Algorand\Tests\Unit\Mnemonic;

use Orchestra\Testbench\TestCase;
use Rootsoft\Algorand\Exceptions\MnemonicException;
use Rootsoft\Algorand\Exceptions\WordListException;
use Rootsoft\Algorand\Mnemonic\Mnemonic;
use Rootsoft\Algorand\Mnemonic\WordList;
use Rootsoft\Algorand\Utils\Buffer;
use Rootsoft\Algorand\Utils\CryptoUtils;

class MnemonicTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testZeroVector()
    {
        $zeroKeys = Buffer::toBinaryString(CryptoUtils::fillBytes(0));
        $expectedWords = 'abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon invest';
        $seedphrase = Mnemonic::Entropy(bin2hex($zeroKeys));
        $this->assertEquals($seedphrase->words, explode(' ', $expectedWords));

        // Seed
        $seed = Mnemonic::Words($seedphrase->words);
        $this->assertEquals(hex2bin($seed->entropy), $zeroKeys);
    }

    public function testWordNotInList()
    {
        $this->expectException(WordListException::class);
        $words = 'abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon zzz invest';
        Mnemonic::Words($words);
    }

    public function testGenerateAndRecovery()
    {
        for ($i = 0; $i < 1000; $i++) {
            $bytes = random_bytes(32);
            $seedphrase = Mnemonic::Entropy(bin2hex($bytes));
            $regenKey = Mnemonic::Words($seedphrase->words);
            $this->assertEquals($seedphrase->entropy, $regenKey->entropy);
        }
    }

    public function testCorruptedChecksum()
    {
        for ($i = 0; $i < 1000; $i++) {
            $bytes = random_bytes(32);
            $words = Mnemonic::Entropy(bin2hex($bytes))->words;
            $oldWord = $words[count($words) - 1];
            $newWord = $oldWord;
            while ($oldWord == $newWord) {
                $newWord = WordList::English()->getWord(random_int(0, 2 ^ 11));
            }

            $words[count($words) - 1] = $newWord;
            $expectedException = false;
            try {
                Mnemonic::Words($words);
            } catch (MnemonicException $ex) {
                $expectedException = true;
            }

            $this->assertTrue($expectedException);
        }
    }

    public function testInvalidKeyLength()
    {
        $badLengths = [1, 31, 33, 100, 35, 2, 30];
        foreach ($badLengths as $badLen) {
            $randomBytes = random_bytes($badLen);

            $expectedException = false;
            try {
                Mnemonic::Entropy(bin2hex($randomBytes));
            } catch (MnemonicException $ex) {
                $expectedException = true;
            }

            $this->assertTrue($expectedException);
        }
    }
}
