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
        $program2 = $program.$keccakx800;
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
}
