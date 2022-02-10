<?php

namespace Rootsoft\Algorand\Tests\Unit\Logic;

use Orchestra\Testbench\TestCase;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Crypto\Logic;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Utils\Buffer;
use Rootsoft\Algorand\Utils\CryptoUtils;

class LogicTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testParseUVarint1()
    {
        $data = [0x01];
        $result = Logic::getUVarint($data, 0);
        $this->assertEquals($result->getLength(), 1);
        $this->assertEquals($result->getValue(), 1);
    }

    public function testParseUVarint2()
    {
        $data = [0x02];
        $result = Logic::getUVarint($data, 0);
        $this->assertEquals($result->getLength(), 1);
        $this->assertEquals($result->getValue(), 2);
    }

    public function testParseUVarint3()
    {
        $data = [0x7b];
        $result = Logic::getUVarint($data, 0);
        $this->assertEquals($result->getLength(), 1);
        $this->assertEquals($result->getValue(), 123);
    }

    public function testParseUVarint4()
    {
        $data = [0xc8, 0x03];
        $result = Logic::getUVarint($data, 0);
        $this->assertEquals($result->getLength(), 2);
        $this->assertEquals($result->getValue(), 456);
    }

    public function testParseUVarint4Offset()
    {
        $data = [0x0, 0x0, 0xc8, 0x03];
        $result = Logic::getUVarint($data, 2);
        $this->assertEquals($result->getLength(), 2);
        $this->assertEquals($result->getValue(), 456);
    }

    public function testParseIntcBlock()
    {
        $data = [0x20, 0x05, 0x00, 0x01, 0xc8, 0x03, 0x7b, 0x02];
        $result = Logic::readIntConstBlock($data, 0);
        $this->assertEquals($result->getSize(), count($data));
        $this->assertEquals($result->getResults(), [0, 1, 456, 123, 2]);
    }

    public function testParseBytecBlock()
    {
        $data = Buffer::toArray(Base64::decode('JgINMTIzNDU2Nzg5MDEyMwIBAg=='));
        $values = [Buffer::toArray(Base64::decode('MTIzNDU2Nzg5MDEyMw==')), [0x1, 0x2]];

        $result = Logic::readByteConstBlock($data, 0);
        $this->assertEquals($result->getSize(), count($data));
        $this->assertEquals($result->getResults(), $values);
    }

    public function testParsePushIntOp()
    {
        $data = [0x81, 0x80, 0x80, 0x04];
        $result = Logic::readPushIntOp($data, 0);
        $this->assertEquals($result->getSize(), count($data));
        $this->assertEquals($result->getResults(), [65536]);
    }

    public function testParsePushBytesOp()
    {
        $data = Buffer::toArray(Base64::decode('gAtoZWxsbyB3b3JsZA=='));
        $values = [Buffer::toArray(utf8_decode('hello world'))];

        $result = Logic::readPushByteOp($data, 0);
        $this->assertEquals($result->getSize(), count($data));
        $this->assertEquals($result->getResults(), $values);
    }

    public function testCheckProgramValid()
    {
        $program = Buffer::toBinaryString([0x01, 0x20, 0x01, 0x01, 0x22]);
        $programData = Logic::readProgram($program);
        $this->assertTrue($programData->good);
        $this->assertEquals($programData->intBlock, [1]);
        $this->assertEmpty($programData->byteBlock);

        // With arguments
        $arguments = CryptoUtils::fillBytes(0x31, 10);
        $programData = Logic::readProgram($program, $arguments);
        $this->assertTrue($programData->good);
        $this->assertEquals($programData->intBlock, [1]);
        $this->assertEmpty($programData->byteBlock);
    }

    public function testCheckProgramArgsTooLong()
    {
        $this->expectException(AlgorandException::class);
        $program = Buffer::toBinaryString([0x01, 0x20, 0x01, 0x01, 0x22]);
        $arguments = CryptoUtils::fillBytes(0x31, 1000);
        Logic::readProgram($program, $arguments);
    }

    public function testCheckProgramTooLong()
    {
        $this->expectException(AlgorandException::class);
        $program = Buffer::toBinaryString([
            ...[0x01, 0x20, 0x01, 0x01, 0x22],
            ...CryptoUtils::fillBytes(0, 1000),
        ]);
        Logic::readProgram($program);
    }

    public function testCheckProgramInvalidOpcode()
    {
        $this->expectException(AlgorandException::class);
        $program = Buffer::toBinaryString([0x01, 0x20, 0x01, 0x01, 0xFF]);
        Logic::readProgram($program);
    }

    public function testCheckProgramCost()
    {
        $oldVersions = [0x1, 0x2, 0x3];
        $versions = [0x4];
        $program = Buffer::toBinaryString([0x01, 0x26, 0x01, 0x01, 0x01, 0x28, 0x02]);
        $args = [utf8_encode('aaaaaaaaaa')];

        $programData = Logic::readProgram($program, $args);
        $this->assertTrue($programData->good);

        $keccakx800 = Buffer::toBinaryString(CryptoUtils::fillBytes(0x02, 800));
        $program2 = $program . $keccakx800;
        foreach ($oldVersions as $v) {
            $program2[0] = chr($v);
            $this->testBadProgram($program2, $args);
        }

        foreach ($versions as $v) {
            $program2[0] = chr($v);
            $programData = Logic::readProgram($program2, $args);
            $this->assertTrue($programData->good);
        }
    }

    private function testBadProgram(string $program, array $args)
    {
        $exceptionThrown = false;

        try {
            Logic::readProgram($program, $args);
        } catch (AlgorandException $ex) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    public function testCheckProgramTEALV2()
    {
        $this->assertGreaterThanOrEqual(2, Logic::evalMaxVersion());
        $this->assertGreaterThanOrEqual(2, Logic::logicSigVersion());

        // int 0; balance
        $program = Buffer::toBinaryString([0x02, 0x20, 0x01, 0x00, 0x22, 0x60]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);

        // int 0; int 0; app_opted_in
        $program = Buffer::toBinaryString([0x02, 0x20, 0x01, 0x00, 0x22, 0x22, 0x61]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);

        // int 0; int 0; asset_holding_get Balance
        $program = Buffer::toBinaryString([0x02, 0x20, 0x01, 0x00, 0x22, 0x70, 0x00]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);
    }

    public function testCheckProgramTEALV3()
    {
        $this->assertGreaterThanOrEqual(3, Logic::evalMaxVersion());
        $this->assertGreaterThanOrEqual(3, Logic::logicSigVersion());

        // min_balance
        $program = Buffer::toBinaryString([0x03, 0x20, 0x01, 0x00, 0x22, 0x78]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);

        // int 0; pushbytes "hi"; pop
        $program = Buffer::toBinaryString([0x03, 0x20, 0x01, 0x00, 0x22, 0x80, 0x02, 0x68, 0x69, 0x48]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);

        // int 0; pushint 1; pop
        $program = Buffer::toBinaryString([0x03, 0x20, 0x01, 0x00, 0x22, 0x81, 0x01, 0x48]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);

        // int 0; int 1; swap; pop
        $program = Buffer::toBinaryString([0x03, 0x20, 0x02, 0x00, 0x01, 0x22, 0x23, 0x4c, 0x48]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);
    }

    public function testCheckProgramTEALV4()
    {
        $this->assertGreaterThanOrEqual(4, Logic::evalMaxVersion());

        // divmodw
        $program = Buffer::toBinaryString([0x04, 0x20, 0x03, 0x01, 0x00, 0x02, 0x22, 0x81, 0xd0, 0x0f, 0x23, 0x24, 0x1f]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);

        // gloads i
        $program = Buffer::toBinaryString([0x04, 0x20, 0x01, 0x00, 0x22, 0x3b, 0x00]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);

        // callsub
        $program = Buffer::toBinaryString([0x04, 0x20, 0x02, 0x01, 0x02, 0x22, 0x88, 0x00, 0x02, 0x23, 0x12, 0x49]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);

        // b>=
        $program = Buffer::toBinaryString([0x04, 0x26, 0x02, 0x01, 0x11, 0x01, 0x10, 0x28, 0x29, 0xa7]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);

        // b^
        $program = Buffer::toBinaryString([0x04, 0x26, 0x02, 0x01, 0x11, 0x01, 0x10, 0x28, 0x29, 0xa7]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);

        // callsub, retsub.
        $program = Buffer::toBinaryString([0x04, 0x20, 0x02, 0x01, 0x02, 0x22, 0x88, 0x00, 0x03, 0x23, 0x12, 0x43, 0x49, 0x08, 0x89]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);

        // loop
        $program = Buffer::toBinaryString([0x04, 0x20, 0x04, 0x01, 0x02, 0x0a, 0x10, 0x22, 0x23, 0x0b, 0x49, 0x24, 0x0c, 0x40, 0xff, 0xf8, 0x25, 0x12]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);
    }

    public function testCheckProgramTEALV5()
    {
        $this->assertGreaterThanOrEqual(5, Logic::evalMaxVersion());

        // itxn ops
        $program = Buffer::toBinaryString([0x05, 0x20, 0x01, 0xc0, 0x84, 0x3d, 0xb1, 0x81, 0x01, 0xb2, 0x10, 0x22,
            0xb2, 0x08, 0x31, 0x00, 0xb2, 0x07, 0xb3, 0xb4, 0x08, 0x22, 0x12]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);

        // ECDSA ops
        $program = Buffer::toBinaryString([
            0x05, 0x80, 0x08, 0x74, 0x65, 0x73, 0x74, 0x64, 0x61, 0x74, 0x61, 0x03,
            0x80, 0x20, 0x79, 0xbf, 0xa8, 0x24, 0x5a, 0xea, 0xc0, 0xe7, 0x14, 0xb7,
            0xbd, 0x2b,
            0x32, 0x52,
            0xd0, 0x39, 0x79, 0xe5, 0xe7, 0xa4, 0x3c, 0xb0, 0x39, 0x71, 0x5a, 0x5f,
            0x81, 0x09,
            0xa7, 0xdd, 0x9b, 0xa1, 0x80, 0x20, 0x07, 0x53, 0xd3, 0x17, 0xe5, 0x43,
            0x50, 0xd1,
            0xd1, 0x02, 0x28, 0x9a, 0xfb, 0xde, 0x30, 0x02, 0xad, 0xd4, 0x52, 0x9f,
            0x10, 0xb9,
            0xf7, 0xd3, 0xd2, 0x23, 0x84, 0x39, 0x85, 0xde, 0x62, 0xe0, 0x80, 0x21,
            0x03, 0xab,
            0xfb, 0x5e, 0x6e, 0x33, 0x1f, 0xb8, 0x71, 0xe4, 0x23, 0xf3, 0x54, 0xe2,
            0xbd, 0x78,
            0xa3, 0x84, 0xef, 0x7c, 0xb0, 0x7a, 0xc8, 0xbb, 0xf2, 0x7d, 0x2d, 0xd1,
            0xec, 0xa0,
            0x0e, 0x73, 0xc1, 0x06, 0x00, 0x05, 0x00
        ]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);

        // cover, uncover, log
        $program = Buffer::toBinaryString([0x05, 0x80, 0x01, 0x61, 0x80, 0x01, 0x62, 0x80, 0x01, 0x63, 0x4e, 0x02,
            0x4f, 0x02, 0x50, 0x50, 0xb0, 0x81, 0x01]);
        $valid = Logic::checkProgram($program);
        $this->assertTrue($valid);
    }
}
