<?php


namespace Rootsoft\Algorand\Tests\Unit\Crypto;

use Orchestra\Testbench\TestCase;
use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Crypto\Signature;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;

class LogicSigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

    }

    public function testLogicSigCreation()
    {
        $program = pack('C*', ...[0x01, 0x20, 0x01, 0x01, 0x22]);
        $programHash = '6Z3C3LDVWGMX23BMSYMANACQOSINPFIRF77H7N3AWJZYV6OH6GWTJKVMXY';
        $sender = Address::fromAlgorandAddress($programHash);
        $lsig = new LogicSignature($program);

        $this->assertEquals($lsig->getLogic(), $program);
        $this->assertNull($lsig->getArguments());
        $this->assertNull($lsig->getSignature());
        $this->assertNull($lsig->getMultiSignature());

        $verified = $lsig->verify($sender);
        $this->assertTrue($verified);
        $this->assertEquals($lsig->toAddress(), $sender);
    }

    public function testLogicSigSignature()
    {
        $hex = '43c1ecbb7ff1bf19b61f84ce38b95eafde2ae13cae8995be532fb74f88db4b3254cb9eca14d821ea1708d41d62f6fdba6602c4e0158f7066be13d6d54b4a91a1';
        $account = Account::fromSecretKey($hex);

        $program = pack('C*', ...[0x01, 0x20, 0x01, 0x01, 0x22]);
        $programHash = '6Z3C3LDVWGMX23BMSYMANACQOSINPFIRF77H7N3AWJZYV6OH6GWTJKVMXY';
        $sender = Address::fromAlgorandAddress($programHash);

        $lsig = new LogicSignature($program);
        $lsig->sign($account);
        $verified = $lsig->verify($account->getAddress());
    }
}
