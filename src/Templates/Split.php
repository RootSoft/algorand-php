<?php

namespace Rootsoft\Algorand\Templates;

use Brick\Math\BigInteger;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\AtomicTransfer;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\SignedTransaction;
use Rootsoft\Algorand\Templates\Parameters\AddressParameterValue;
use Rootsoft\Algorand\Templates\Parameters\IntParameterValue;
use Rootsoft\Algorand\Utils\Buffer;
use Rootsoft\Algorand\Utils\Encoder;

/**
 * Split allows locking algos in an account which allows transfering to two predefined addresses in a specified ratio
 * such that for the given ratn and ratd parameters we have:.
 *
 * first_recipient_amount * rat_2 == second_recipient_amount * rat_1
 *
 * Split also has an expiry round, after which the owner can transfer back the funds.
 */
class Split
{
    public const REFERENCE_PROGRAM = 'ASAIAQUCAAYHCAkmAyDYHIR7TIW5eM/WAZcXdEDqv7BD+baMN6i2/A5JatGbNCDKsaoZHPQ3Zg8zZB/BZ1oDgt77LGo5np3rbto3/gloTyB40AS2H3I72YCbDk4hKpm7J7NnFy2Xrt39TJG0ORFg+zEQIhIxASMMEDIEJBJAABkxCSgSMQcyAxIQMQglEhAxAiEEDRAiQAAuMwAAMwEAEjEJMgMSEDMABykSEDMBByoSEDMACCEFCzMBCCEGCxIQMwAIIQcPEBA=';

    /**
     * Create a new Split contract.
     * Split allows locking algos in an account which allows transfering to two predefined addresses in a specified ratio
     * such that for the given ratn and ratd parameters we have:.
     *
     * first_recipient_amount * rat_2 == second_recipient_amount * rat_1
     *
     * Split also has an expiry round, after which the owner can transfer back the funds.
     * @param Address $owner
     * @param Address $receiver1
     * @param Address $receiver2
     * @param int $rat1
     * @param int $rat2
     * @param int $expiryRound
     * @param int $minPay
     * @param int $maxFee
     * @return ContractTemplate
     */
    public static function create(
        Address $owner,
        Address $receiver1,
        Address $receiver2,
        int $rat1,
        int $rat2,
        int $expiryRound,
        int $minPay,
        int $maxFee
    ): ContractTemplate {
        $values = [
            new IntParameterValue(4, $maxFee),
            new IntParameterValue(7, $expiryRound),
            new IntParameterValue(8, $rat2),
            new IntParameterValue(9, $rat1),
            new IntParameterValue(10, $minPay),
            new AddressParameterValue(14, $owner),
            new AddressParameterValue(47, $receiver1),
            new AddressParameterValue(80, $receiver2),
        ];

        return ContractTemplate::inject(Base64::decode(self::REFERENCE_PROGRAM), $values);
    }

    /**
     * Get the transactions (as a byte array).
     * Reads and verify the contract.
     *
     * @param ContractTemplate $contract
     * @param int $amount
     * @param int $firstValid
     * @param int $lastValid
     * @param string $genesisHash
     * @param int $feePerByte
     * @return string
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public static function getTransactions(
        ContractTemplate $contract,
        int $amount,
        int $firstValid,
        int $lastValid,
        string $genesisHash,
        int $feePerByte
    ) : string {
        $data = ContractTemplate::readAndVerifyContract($contract->getProgram(), 8, 3);

        $rat1 = $data->intBlock[6];
        $rat2 = $data->intBlock[5];
        $minTrade = $data->intBlock[7];

        $fraction = ($rat1 / ($rat1 + $rat2));
        $amountReceiverOne = round($fraction * $amount);
        $amountReceiverTwo = round(((1.0 - $fraction) * $amount));
        $diff = $amount - $amountReceiverOne - $amountReceiverTwo;

        if ($diff != 0) {
            throw new AlgorandException("Unable to exactly split $amount using the contract ratio of $rat1 / $rat2");
        }

        if ($amountReceiverOne < $minTrade) {
            throw new AlgorandException("Receiver one must receive at least $minTrade");
        }

        $rcv1 = BigInteger::of($amountReceiverOne)->multipliedBy(BigInteger::of($rat2));
        $rcv2 = BigInteger::of($amountReceiverTwo)->multipliedBy(BigInteger::of($rat1));
        if ($rcv1 != $rcv2) {
            throw new AlgorandException('The token split must be exactly');
        }

        $receiver1 = Address::fromPublicKey(Buffer::toBinaryString($data->byteBlock[1]));
        $receiver2 = Address::fromPublicKey(Buffer::toBinaryString($data->byteBlock[2]));

        // fee = 2220000
        $tx1 = TransactionBuilder::payment()
            ->sender($contract->getAddress())
            ->receiver($receiver1)
            ->suggestedFeePerByte($feePerByte)
            ->amount($amountReceiverOne)
            ->firstValid($firstValid)
            ->lastValid($lastValid)
            ->genesisHashB64($genesisHash)
            ->build();

        $tx2 = TransactionBuilder::payment()
            ->sender($contract->getAddress())
            ->receiver($receiver2)
            ->suggestedFeePerByte($feePerByte)
            ->amount($amountReceiverTwo)
            ->firstValid($firstValid)
            ->lastValid($lastValid)
            ->genesisHashB64($genesisHash)
            ->build();

        $lsig = new LogicSignature($contract->getProgram());
        AtomicTransfer::group([$tx1, $tx2]);

        $signedTx1 = SignedTransaction::fromLogicSignature($tx1, $lsig);
        $signedTx2 = SignedTransaction::fromLogicSignature($tx2, $lsig);

        $encoded1 = Encoder::getInstance()->encodeMessagePack($signedTx1->toMessagePack());
        $encoded2 = Encoder::getInstance()->encodeMessagePack($signedTx2->toMessagePack());

        return $encoded1.$encoded2;
    }
}
