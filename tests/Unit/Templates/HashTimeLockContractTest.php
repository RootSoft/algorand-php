<?php


namespace Rootsoft\Algorand\Tests\Unit\Templates;

use Orchestra\Testbench\TestCase;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Crypto\Logic;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Templates\HashTimeLockContract;

class HashTimeLockContractTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testVarInt()
    {
        $a = 600000;
        $buffer = Logic::putUVarint($a);
        $result = Logic::getUVarint($buffer, 0);
        $this->assertEquals($result->getValue(), $a);
        //$this->assertEquals($result->getValue(), $a);
    }

    public function testHashTimeLockContract()
    {
        $goldenAddress = 'FBZIR3RWVT2BTGVOG25H3VAOLVD54RTCRNRLQCCJJO6SVSCT5IVDYKNCSU';
        $goldenProgram = 'ASAE6AcBAMDPJCYDIOaalh5vLV96yGYHkmVSvpgjXtMzY8qIkYu5yTipFbb5IBB2YRNPIfx8AiI9UKues2ALw//DcSQjoeR7sfmp2/VfIP68oLsUSlpOp7Q4pGgayA5soQW8tgf8VlMlyVaV9qITMQEiDjEQIxIQMQcyAxIQMQgkEhAxCSgSLQEpEhAxCSoSMQIlDRAREA==';
        $goldenTx = 'gqRsc2lngqNhcmeRxAhwcmVpbWFnZaFsxJcBIAToBwEAwM8kJgMg5pqWHm8tX3rIZgeSZVK+mCNe0zNjyoiRi7nJOKkVtvkgEHZhE08h/HwCIj1Qq56zYAvD/8NxJCOh5Hux+anb9V8g/ryguxRKWk6ntDikaBrIDmyhBby2B/xWUyXJVpX2ohMxASIOMRAjEhAxBzIDEhAxCCQSEDEJKBItASkSEDEJKhIxAiUNEBEQo3R4boelY2xvc2XEIOaalh5vLV96yGYHkmVSvpgjXtMzY8qIkYu5yTipFbb5o2ZlZc0D6KJmdgGiZ2jEIH+DsWV/8fxTuS3BgUih1l38LUsfo9Z3KErd0gASbZBpomx2ZKNzbmTEIChyiO42rPQZmq42un3UDl1H3kZii2K4CElLvSrIU+oqpHR5cGWjcGF5';

        $owner = Address::fromAlgorandAddress('726KBOYUJJNE5J5UHCSGQGWIBZWKCBN4WYD7YVSTEXEVNFPWUIJ7TAEOPM');
        $receiver = Address::fromAlgorandAddress('42NJMHTPFVPXVSDGA6JGKUV6TARV5UZTMPFIREMLXHETRKIVW34QFSDFRE');
        $image = 'EHZhE08h/HwCIj1Qq56zYAvD/8NxJCOh5Hux+anb9V8=';

        // Create the contract
        $contract = HashTimeLockContract::create($owner, $receiver, 1, $image, 600000, 1000);

        $this->assertEquals($contract->getAddress()->encodedAddress, $goldenAddress);
        $this->assertEquals($contract->getProgram(), Base64::decode($goldenProgram));

        // Create the transactions
        $preImageAsBase64 = 'cHJlaW1hZ2U=';
        $gh = 'f4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGk=';

        $signedTx = HashTimeLockContract::getTransaction($contract, $preImageAsBase64, 1, 100, $gh, 0);

        $this->assertEquals($signedTx->toBase64(), $goldenTx);
    }
}
