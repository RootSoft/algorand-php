<?php


namespace Rootsoft\Algorand\Tests\Unit\Crypto;

use Orchestra\Testbench\TestCase;
use Rootsoft\Algorand\Crypto\Ed25519PublicKey;
use Rootsoft\Algorand\Crypto\Logic;
use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Crypto\MultiSignatureAddress;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Utils\Buffer;

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

    public function testLogicSigInvalidProgram()
    {
        $this->expectException(AlgorandException::class);
        $program = Buffer::toBinaryString([0x07, 0x20, 0x01, 0x01, 0x22]);
        $lsig = new LogicSignature($program);
    }

    public function testLogicSignature()
    {
        $program = Buffer::toBinaryString([0x01, 0x20, 0x01, 0x01, 0x22]);
        $account = Account::random();
        $lsig = new LogicSignature($program);
        $lsig = $lsig->sign($account);

        $this->assertEquals($lsig->getLogic(), $program);
        $this->assertNull($lsig->getArguments());
        $this->assertNotNull($lsig->getSignature());
        $this->assertNull($lsig->getMultiSignature());

        $verified = $lsig->verify($account->getAddress());
        $this->assertTrue($verified);
    }

    public function testLogicSigMultiSignature()
    {
        $program = pack('C*', [0x01, 0x20, 0x01, 0x01, 0x22]);
        $one = Address::fromAlgorandAddress('DN7MBMCL5JQ3PFUQS7TMX5AH4EEKOBJVDUF4TCV6WERATKFLQF4MQUPZTA');
        $two = Address::fromAlgorandAddress('BFRTECKTOOE7A5LHCF3TTEOH2A7BW46IYT2SX5VP6ANKEXHZYJY77SJTVM');
        $three = Address::fromAlgorandAddress('47YPQTIGQEO7T4Y4RWDYWEKV6RTR2UNBQXBABEEGM72ESWDQNCQ52OPASU');

        $publicKeys = array_map(fn (Address $value) => new Ed25519PublicKey($value->address), [$one, $two, $three]);
        $msa = new MultiSignatureAddress(1, 2, $publicKeys);

        $mnemonic1 = 'auction inquiry lava second expand liberty glass involve ginger illness length room item discover ahead table doctor term tackle cement bonus profit right above catch';
        $mnemonic2 = 'since during average anxiety protect cherry club long lawsuit loan expand embark forum theory winter park twenty ball kangaroo cram burst board host ability left';

        $account1 = Account::mnemonic($mnemonic1);
        $account2 = Account::mnemonic($mnemonic2);
        $account3 = Account::random();

        $lsig = new LogicSignature($program);
        $lsig = $lsig->sign($account1, $msa);
        $this->assertEquals($lsig->getLogic(), $program);
        $this->assertNull($lsig->getArguments());
        $this->assertNull($lsig->getSignature());
        $this->assertNotNull($lsig->getMultiSignature());

        $verified = $lsig->verify($msa->toAddress());
        $this->assertFalse($verified);
        $lsig = $lsig->append($account2);
        $verified = $lsig->verify($msa->toAddress());
        $this->assertTrue($verified);

        // Add a single signature and ensure it fails
        $lsig1 = new LogicSignature($program);
        $lsig1 = $lsig1->sign($account3);
        $lsig3 = new LogicSignature($program, null, $lsig1->getSignature());
        $verified = $lsig3->verify($msa->toAddress());
        $this->assertFalse($verified);
        $verified = $lsig3->verify($account3->getAddress());
        $this->assertTrue($verified);
    }
}
