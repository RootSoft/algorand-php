<?php

namespace Rootsoft\Algorand\Templates;

use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\SignedTransaction;
use Rootsoft\Algorand\Templates\Parameters\AddressParameterValue;
use Rootsoft\Algorand\Templates\Parameters\BytesParameterValue;
use Rootsoft\Algorand\Templates\Parameters\IntParameterValue;
use Rootsoft\Algorand\Utils\Buffer;
use Rootsoft\Algorand\Utils\CryptoUtils;

/**
 * Hash Time Locked Contract allows a user to receive the Algo prior to a deadline (in terms of a round) by proving
 * knowledge of a special value or to forfeit the ability to claim, returning it to the payer.
 *
 * This contract is usually used to perform cross-chained atomic swaps.
 * More formally, algos can be transfered under only two circumstances:
 * 1. To receiver if hash_function(arg_0) = hash_value
 * 2. To owner if txn.FirstValid &gt; expiry_round
 */
class HashTimeLockContract
{
    const REFERENCE_PROGRAM = 'ASAEBQEABiYDIP68oLsUSlpOp7Q4pGgayA5soQW8tgf8VlMlyVaV9qITAQYg5pqWHm8tX3rIZgeSZVK+mCNe0zNjyoiRi7nJOKkVtvkxASIOMRAjEhAxBzIDEhAxCCQSEDEJKBItASkSEDEJKhIxAiUNEBEQ';

    /**
     * Create a new Hash Time Locked Contract.
     *
     * This allows a user to receive Algo prior to a deadline by proving knowledge of a special value or to forfeit
     * the ability to claim, returning it to the payer.
     * @param Address $owner
     * @param Address $receiver
     * @param int $hashFunction
     * @param string $hashImage
     * @param int $expiryRound
     * @param int $maxFee
     * @return ContractTemplate
     */
    public static function create(
        Address $owner,
        Address $receiver,
        int $hashFunction,
        string $hashImage,
        int $expiryRound,
        int $maxFee
    ): ContractTemplate {
        $values = [
            new IntParameterValue(3, $maxFee),
            new IntParameterValue(6, $expiryRound),
            new AddressParameterValue(10, $receiver),
            BytesParameterValue::fromBase64(42, $hashImage),
            new AddressParameterValue(45, $owner),
            new IntParameterValue(102, $hashFunction),
        ];

        return ContractTemplate::inject(Base64::decode(self::REFERENCE_PROGRAM), $values);
    }

    /**
     * Read and verify the contract.
     * Returns the signed transaction by the program.
     *
     * @param ContractTemplate $contract
     * @param string $preImage
     * @param int $firstValid
     * @param int $lastValid
     * @param string $genesisHash
     * @param int $feePerByte
     * @return SignedTransaction
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public static function getTransaction(
        ContractTemplate $contract,
        string $preImage,
        int $firstValid,
        int $lastValid,
        string $genesisHash,
        int $feePerByte
    ) : SignedTransaction {
        $data = ContractTemplate::readAndVerifyContract($contract->getProgram(), 4, 3);

        $receiver = Address::fromPublicKey(Buffer::toBinaryString($data->byteBlock[0]));
        $hashImage = Buffer::toBinaryString($data->byteBlock[1]);

        $computedImage = CryptoUtils::sha256(Base64::decode($preImage));

        if ($computedImage != $hashImage) {
            throw new AlgorandException('Unable to verify SHA-256 preImage');
        }

        $tx = TransactionBuilder::payment()
            ->sender($contract->getAddress())
            ->firstValid($firstValid)
            ->lastValid($lastValid)
            ->genesisHashB64($genesisHash)
            ->amount(0)
            ->suggestedFeePerByte($feePerByte)
            ->closeRemainderTo($receiver)
            ->build();

        $args = [Base64::decode($preImage)];
        $lsig = new LogicSignature($contract->getProgram(), $args);

        return SignedTransaction::fromLogicSignature($tx, $lsig);
    }
}
