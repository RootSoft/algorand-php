<?php

namespace Rootsoft\Algorand\Templates;

use http\Exception\InvalidArgumentException;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\SignedTransaction;
use Rootsoft\Algorand\Templates\Parameters\AddressParameterValue;
use Rootsoft\Algorand\Templates\Parameters\BytesParameterValue;
use Rootsoft\Algorand\Templates\Parameters\IntParameterValue;
use Rootsoft\Algorand\Utils\Buffer;

/**
 * PeriodicPayment contract enables creating an account which allows the withdrawal of a fixed amount of assets every
 * fixed number of rounds to a specific Algrorand Address. In addition, the contract allows to add an expiryRound,
 * after which the address can withdraw the rest of the assets.
 */
class PeriodicPayment
{
    public const REFERENCE_PROGRAM = 'ASAHAQoLAAwNDiYCAQYg/ryguxRKWk6ntDikaBrIDmyhBby2B/xWUyXJVpX2ohMxECISMQEjDhAxAiQYJRIQMQQhBDECCBIQMQYoEhAxCTIDEjEHKRIQMQghBRIQMQkpEjEHMgMSEDECIQYNEDEIJRIQERA=';

    /**
     * Create a new Periodic Payment.
     *
     * PeriodicPayment contract enables creating an account which allows the withdrawal of a fixed amount of assets every
     * fixed number of rounds to a specific Algrorand Address. In addition, the contract allows to add an expiryRound,
     * after which the address can withdraw the rest of the assets.
     *
     * @param Address $receiver
     * @param int $amount
     * @param int $withdrawingWindow
     * @param int $period
     * @param int $fee
     * @param int $expiryRound
     * @param string|null $lease
     * @return ContractTemplate
     * @throws \Exception
     */
    public static function create(
        Address $receiver,
        int $amount,
        int $withdrawingWindow,
        int $period,
        int $fee,
        int $expiryRound,
        ?string $lease = null
    ): ContractTemplate {
        $values = [
            new IntParameterValue(4, $fee),
            new IntParameterValue(5, $period),
            new IntParameterValue(7, $withdrawingWindow),
            new IntParameterValue(8, $amount),
            new IntParameterValue(9, $expiryRound),
            BytesParameterValue::fromBase64(12, $lease ?? random_bytes(32)),
            new AddressParameterValue(15, $receiver),
        ];

        return ContractTemplate::inject(Base64::decode(self::REFERENCE_PROGRAM), $values);
    }

    /**
     * Read and verify the contract.
     * Returns the signed transaction by the program.
     *
     * @param ContractTemplate $contract
     * @param int $firstValid
     * @param string $genesisHash
     * @param int $feePerByte
     * @return SignedTransaction
     * @throws \Rootsoft\Algorand\Exceptions\AlgorandException
     * @throws \SodiumException
     */
    public static function getWithdrawalTransaction(
        ContractTemplate $contract,
        int $firstValid,
        string $genesisHash,
        int $feePerByte
    ) : SignedTransaction {
        $data = ContractTemplate::readAndVerifyContract($contract->getProgram(), 7, 2);

        $period = $data->intBlock[2];
        $withdrawingWindow = $data->intBlock[4];
        $amount = $data->intBlock[5];
        $lease = Buffer::toBinaryString($data->byteBlock[0]);
        $receiver = Address::fromPublicKey(Buffer::toBinaryString($data->byteBlock[1]));

        if ($firstValid % $period != 0) {
            throw new InvalidArgumentException('invalid contract: firstValid must be divisible by the period');
        }

        $transaction = TransactionBuilder::payment()
            ->sender($contract->getAddress())
            ->receiver($receiver)
            ->suggestedFeePerByte($feePerByte)
            ->firstValid($firstValid)
            ->lastValid($firstValid + $withdrawingWindow)
            ->amount($amount)
            ->genesisHashB64($genesisHash)
            ->lease($lease)
            ->build();

        $lsig = new LogicSignature($contract->getProgram());

        return $lsig->signTransaction($transaction);
    }
}
