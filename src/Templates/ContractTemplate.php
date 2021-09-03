<?php

namespace Rootsoft\Algorand\Templates;

use Rootsoft\Algorand\Crypto\Logic;
use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Crypto\ProgramData;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Applications\TEALProgram;
use Rootsoft\Algorand\Templates\Parameters\ParameterValue;
use Rootsoft\Algorand\Utils\Buffer;

/**
 * Hash Time Locked Contract allows a user to receive the Algo prior to a deadline (in terms of a round) by proving
 * knowledge of a special value or to forfeit the ability to claim, returning it to the payer.
 *
 * This contract is usually used to perform cross-chained atomic swaps.
 * More formally, algos can be transfered under only two circumstances:
 * 1. To receiver if hash_function(arg_0) = hash_value
 * 2. To owner if txn.FirstValid &gt; expiry_round
 */
class ContractTemplate
{
    private Address $address;
    private string $program;

    public function __construct(LogicSignature  $signature)
    {
        $this->address = $signature->toAddress();
        $this->program = $signature->getLogic();
    }

    /**
     * Create a new contract template.
     *
     * @return ContractTemplate
     */
    public static function fromProgram(TEALProgram $program)
    {
        return new self(LogicSignature::fromProgram($program));
    }

    /**
     * Inject the values in the given program.
     *
     * @param string $program
     * @param array|ParameterValue[] $values
     * @return ContractTemplate
     */
    public static function inject(string $program, array $values): ContractTemplate
    {
        $program = Buffer::toArray($program);
        $updatedProgram = [];
        $idx = 0;
        foreach ($values as $value) {
            while ($idx < $value->getOffset()) {
                $updatedProgram[] = $program[$idx++];
            }
            foreach ($value->toBytes() as $b) {
                $updatedProgram[] = $b;
            }
            $idx += $value->placeholderSize();
        }

        // append remainder of program.
        while ($idx < count($program)) {
            $updatedProgram[] = $program[$idx++];
        }

        $lsig = new LogicSignature(Buffer::toBinaryString($updatedProgram));

        return new ContractTemplate($lsig);
    }

    /**
     * Read and verify the contract.
     *
     * @param string $program
     * @param int $numInts
     * @param int $numByteArrays
     * @return ProgramData
     * @throws AlgorandException
     */
    public static function readAndVerifyContract(string $program, int $numInts, int $numByteArrays) : ProgramData
    {
        $data = Logic::readProgram($program);
        if (! $data->good ||
            count($data->intBlock) != $numInts ||
            count($data->byteBlock) != $numByteArrays) {
            throw new AlgorandException('Invalid contract detected');
        }

        return $data;
    }

    /**
     * Get the address of this stateless smart contract.
     *
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * Get the reference program.
     *
     * @return string
     */
    public function getProgram(): string
    {
        return $this->program;
    }
}
