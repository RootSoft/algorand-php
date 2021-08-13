<?php

namespace Rootsoft\Algorand\Crypto;

use JsonMapper\JsonMapperFactory;
use JsonMapper\Middleware\CaseConversion;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Utils\Buffer;

class Logic
{
    const MAX_COST = 20000;
    const MAX_LENGTH = 1000;

    const INTCBLOCK_OPCODE = 32;
    const BYTECBLOCK_OPCODE = 38;
    const PUSHBYTES_OPCODE = 128;
    const PUSHINT_OPCODE = 129;

    private static ?LangSpec $langSpec = null;

    /**
     * Perform basic program validation; instruction count and program cost.
     * @param string $program
     * @param array $arguments
     * @return bool
     */
    public static function checkProgram(string $program, ?array $arguments = null):bool
    {
        return self::readProgram($program, $arguments)->good;
    }

    /**
     * Performs basic program validation: instruction count and program cost
     *
     * @param string $program
     * @param array $arguments
     * @return ProgramData
     * @throws AlgorandException
     */
    public static function readProgram(string $program, ?array $arguments = null): ProgramData
    {
        $ints = [];
        $bytes = [];

        if (is_null(self::$langSpec)) {
            self::loadLangSpec();
        }

        $buffer = Buffer::toArray($program);
        $result = self::getUVarint($buffer, 0);
        $vlen = $result->getLength();
        $version = $result->getValue();
        if ($vlen <= 0) {
            throw new AlgorandException('Version parsing error');
        }

        if ($version > self::$langSpec->evalMaxVersion) {
            throw new AlgorandException('Unsupported version');
        }

        $cost = 0;
        $length = strlen($program);
        if (isset($arguments)) {
            for ($i = 0; $i < count($arguments); $i++) {
                $length += strlen($arguments[$i]);
            }
        }

        if ($length > self::MAX_LENGTH) {
            throw new AlgorandException('Program too long');
        }

        // @var Operation[]
        $opcodes = array_fill(0, 256, null);
        for ($i = 0; $i < count(self::$langSpec->ops); $i++) {
            $op = self::$langSpec->ops[$i];
            $opcodes[$op->opcode] = $op;
        }

        $pc = $vlen;

        while ($pc < count($buffer)) {
            $opcode = $buffer[$pc];
            $op = $opcodes[$opcode];
            if (is_null($op)) {
                throw new AlgorandException("Invalid instruction: $op");
            }

            $cost += $op->cost;
            $size = $op->size;
            if ($size == 0) {
                switch ($op->opcode) {
                    case self::INTCBLOCK_OPCODE:
                        $intsBlock = self::readIntConstBlock($buffer, $pc);
                        $size += $intsBlock->getSize();
                        array_push($ints, ...$intsBlock->getResults());
                        break;
                    case self::BYTECBLOCK_OPCODE:
                        $bytesBlock = self::readByteConstBlock($buffer, $pc);
                        $size += $bytesBlock->getSize();
                        array_push($bytes, ...$bytesBlock->getResults());
                        break;
                    case self::PUSHINT_OPCODE:
                        $pushInt = self::readPushIntOp($buffer, $pc);
                        $size += $pushInt->getSize();
                        array_push($ints, ...$pushInt->getResults());
                        break;
                    case self::PUSHBYTES_OPCODE:
                        $pushBytes = self::readPushByteOp($buffer, $pc);
                        $size += $pushBytes->getSize();
                        array_push($bytes, ...$pushBytes->getResults());
                        break;
                    default:
                        throw new AlgorandException('Invalid instruction');
                }
            }

            $pc += $size;
        }

        if ($version < 4 && $cost > self::MAX_COST) {
            throw new AlgorandException('program too costly for Teal version < 4. consider using v4.');
        }

        return new ProgramData(true, $ints, $bytes);
    }

    private static function loadLangSpec()
    {
        if (isset(self::$langSpec)) {
            return;
        }

        $langSpecFile = sprintf('%1$s%2$slangspec.json', __DIR__, DIRECTORY_SEPARATOR);

        if (! file_exists($langSpecFile) || ! is_readable($langSpecFile)) {
            throw new AlgorandException('langspec not found');
        }

        $jsonString = file_get_contents($langSpecFile);

        $jsonMapper = (new JsonMapperFactory())->bestFit();
        $jsonMapper->push(new CaseConversion(
            \JsonMapper\Enums\TextNotation::KEBAB_CASE(),
            \JsonMapper\Enums\TextNotation::CAMEL_CASE()
        ));

        self::$langSpec = new LangSpec();
        $jsonMapper->mapObjectFromString($jsonString, self::$langSpec);
    }

    /**
     * Given a varint, get the integer value
     *
     * @param string $buffer
     * @param int $bufferOffset
     * @return VarintResult
     */
    public static function getUVarint(array $buffer, int $bufferOffset) :VarintResult
    {
        $x = 0;
        $s = 0;

        for ($i = 0; $i < count($buffer); $i++) {
            $b = $buffer[$bufferOffset + $i];
            if ($b < 0x80) {
                if ($i > 9 || $i == 9 && $b > 1) {
                    return new VarintResult(0, -($i + 1));
                }

                return new VarintResult($x | ($b & 0xff) << $s, $i + 1);
            }
            $x |= (($b & 0x7f) & 0xff) << $s;
            $s += 7;
        }

        return new VarintResult();
    }

    public static function readIntConstBlock(array $buffer, int $pc) :IntConstBlock
    {
        $results = [];
        $size = 1;

        $result = self::getUVarint($buffer, $pc + $size);
        if ($result->getLength() <= 0) {
            throw new AlgorandException("could not decode int const block at pc=$pc");
        }

        $size += $result->getLength();
        $numInts = $result->getValue();
        for ($i = 0; $i < $numInts; $i++) {
            if ($pc + $size >= count($buffer)) {
                throw new AlgorandException('int const block exceeds program length');
            }

            $result = self::getUVarint($buffer, $pc + $size);
            if ($result->getLength() <= 0) {
                throw new AlgorandException("could not decode int const[" . $i . "] block at pc=" . ($pc + $size));
            }
            $size += $result->getLength();
            $results[] = $result->getValue();
        }

        return new IntConstBlock($size, $results);
    }

    public static function readByteConstBlock(array $buffer, int $pc) : ByteConstBlock
    {
        $results = [];
        $size = 1;

        $result = self::getUVarint($buffer, $pc + $size);
        if ($result->getLength() <= 0) {
            throw new AlgorandException("could not decode byte[] const block at pc=$pc");
        }

        $size += $result->getLength();
        $numInts = $result->getValue();
        for ($i = 0; $i < $numInts; $i++) {
            if ($pc + $size >= count($buffer)) {
                throw new AlgorandException('byte[] const block exceeds program length');
            }

            $result = self::getUVarint($buffer, $pc + $size);
            if ($result->getLength() <= 0) {
                throw new AlgorandException("could not decode int const[" . $i . "] block at pc=" . ($pc + $size));
            }
            $size += $result->getLength();
            if ($pc + $size + $result->getValue() > count($buffer)) {
                throw new AlgorandException('byte[] const block exceeds program length');
            }
            $b = array_slice($buffer, $pc + $size, $result->getValue());
            $results[] = $b;
            $size += $result->getValue();
        }

        return new ByteConstBlock($size, $results);
    }

    public static function readPushIntOp(array $buffer, int $pc) :IntConstBlock
    {
        $size = 1;
        $result = self::getUVarint($buffer, $pc + $size);
        if ($result->getLength() <= 0) {
            throw new AlgorandException("could not decode push int const at pc=$pc");
        }

        $size += $result->getLength();

        return new IntConstBlock($size, [$result]);
    }

    public static function readPushByteOp(array $buffer, int $pc) :ByteConstBlock
    {
        $size = 1;
        $result = self::getUVarint($buffer, $pc + $size);
        if ($result->getLength() <= 0) {
            throw new AlgorandException("could not decode push byte const at pc=$pc");
        }

        $size += $result->getLength();
        if ($pc + $size + $result->getValue() > count($buffer)) {
            throw new AlgorandException("byte[] const block exceeds program length");
        }

        $b = array_slice($buffer, $pc + $size, $result->getValue());
        $size += $result->getValue();

        return new ByteConstBlock($size, [$b]);
    }
}

class LangSpec
{
    public int $evalMaxVersion;
    public int $logicSignVersion;

    /**
     * @var Operation[]
     */
    public array $ops;
}

class Operation
{
    public int $opcode;
    public string $name;
    public int $cost;
    public int $size;
    public ?string $returns = null;
    public ?array $argEnum = null;
    public ?string $argEnumTypes = null;
    public ?string $doc = null;
    public ?string $immediateNote = null;
    public ?array $group = null;
}

class ProgramData
{
    public bool $good;
    public array $intBlock;
    public array $byteBlock;

    /**
     * @param bool $good
     * @param array $intBlock
     * @param array $byteBlock
     */
    public function __construct(bool $good, array $intBlock, array $byteBlock)
    {
        $this->good = $good;
        $this->intBlock = $intBlock;
        $this->byteBlock = $byteBlock;
    }
}

class VarintResult
{
    private int $value;
    private int $length;

    /**
     * @param int $value
     * @param int $length
     */
    public function __construct(int $value = 0, int $length = 0)
    {
        $this->value = $value;
        $this->length = $length;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }
}

class IntConstBlock
{
    private int $size;

    /**
     * @var array|int[]
     */
    private array $results;

    /**
     * @param int $size
     * @param array|int[] $results
     */
    public function __construct(int $size, array $results)
    {
        $this->size = $size;
        $this->results = $results;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return array|int[]
     */
    public function getResults(): array
    {
        return $this->results;
    }
}

class ByteConstBlock
{
    private int $size;

    /**
     * @var array|string
     */
    private array $results;

    /**
     * @param int $size
     * @param array|int[] $results
     */
    public function __construct(int $size, array $results)
    {
        $this->size = $size;
        $this->results = $results;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return array|string[]
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
