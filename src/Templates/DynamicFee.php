<?php

namespace Rootsoft\Algorand\Templates;

use Brick\Math\BigInteger;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\AtomicTransfer;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;
use Rootsoft\Algorand\Models\Transactions\SignedTransaction;
use Rootsoft\Algorand\Templates\Parameters\AddressParameterValue;
use Rootsoft\Algorand\Templates\Parameters\BytesParameterValue;
use Rootsoft\Algorand\Templates\Parameters\IntParameterValue;
use Rootsoft\Algorand\Utils\Buffer;
use Rootsoft\Algorand\Utils\Encoder;

/**
 * DynamicFee contract allows you to create a transaction without specifying the fee.
 * The fee will be determined at the moment of transfer.
 */
class DynamicFee
{
    const REFERENCE_PROGRAM = 'ASAFAgEFBgcmAyD+vKC7FEpaTqe0OKRoGsgObKEFvLYH/FZTJclWlfaiEyDmmpYeby1feshmB5JlUr6YI17TM2PKiJGLuck4qRW2+QEGMgQiEjMAECMSEDMABzEAEhAzAAgxARIQMRYjEhAxECMSEDEHKBIQMQkpEhAxCCQSEDECJRIQMQQhBBIQMQYqEhA=';

    /**
     * Create a new dynamic fee.
     *
     * DynamicFee contract allows you to create a transaction without specifying the fee.
     * The fee will be determined at the moment of transfer.
     *
     * @param Address $receiver
     * @param int $amount
     * @param int $firstValid
     * @param Address $closeRemainderTo
     * @param int|null $lastValid
     * @param string|null $lease
     * @return ContractTemplate
     * @throws \Exception
     */
    public static function create(
        Address $receiver,
        int $amount,
        int $firstValid,
        Address $closeRemainderTo,
        ?int $lastValid,
        ?string $lease
    ): ContractTemplate {
        $values = [
            new IntParameterValue(5, $amount),
            new IntParameterValue(6, $firstValid),
            new IntParameterValue(7, $lastValid ?? $firstValid + 1000),
            new AddressParameterValue(11, $receiver),
            new AddressParameterValue(44, $closeRemainderTo),
            BytesParameterValue::fromBase64(76, $lease ?? random_bytes(32)),
        ];

        return ContractTemplate::inject(Base64::decode(self::REFERENCE_PROGRAM), $values);
    }

    /**
     * Returns the main transaction and signed logic needed to complete the transfer.
     * These should be sent to the fee payer, who can use get_transactions() to update fields and create the
     * auxiliary transaction.
     * The transaction and logicsig should be sent to the other party as base64 encoded objects.
     *
     * @param ContractTemplate $contract
     * @param Account $sender
     * @param string $genesisHash
     * @return SignedTransaction
     * @throws \Rootsoft\Algorand\Exceptions\AlgorandException
     * @throws \SodiumException
     */
    public static function sign(ContractTemplate $contract, Account $sender, string $genesisHash) : SignedTransaction
    {
        $data = ContractTemplate::readAndVerifyContract($contract->getProgram(), 5, 3);
        $receiver = Address::fromPublicKey(Buffer::toBinaryString($data->byteBlock[0]));
        $closeRemainderTo = Address::fromPublicKey(Buffer::toBinaryString($data->byteBlock[1]));
        $lease = Buffer::toBinaryString($data->byteBlock[2]);

        $amount = $data->intBlock[2];
        $firstValid = $data->intBlock[3];
        $lastValid = $data->intBlock[4];

        $tx = TransactionBuilder::payment()
            ->sender($sender->getAddress())
            ->flatFee(1000)
            ->firstValid($firstValid)
            ->lastValid($lastValid)
            ->genesisHashB64($genesisHash)
            ->amount($amount)
            ->receiver($receiver)
            ->closeRemainderTo($closeRemainderTo)
            ->lease($lease)
            ->build();

        $lsig = new LogicSignature($contract->getProgram());
        $signedLsig = $lsig->sign($sender);

        return SignedTransaction::fromLogicSignature($tx, $signedLsig);
    }

    /**
     * Create and sign the secondary dynamic fee transaction, update transaction fields, and sign as the fee payer;
     * return both transactions ready to be sent.
     *
     * @param RawTransaction $transaction
     * @param LogicSignature $signature
     * @param Account $account
     * @param BigInteger $feePerByte
     * @return string
     * @throws \Rootsoft\Algorand\Exceptions\AlgorandException
     * @throws \SodiumException
     */
    public static function getReimbursementTransactions(RawTransaction  $transaction, LogicSignature $signature, Account $account, BigInteger $feePerByte) : string
    {
        $transaction->setFeeByFeePerByte($feePerByte);

        $reimbursement = TransactionBuilder::payment()
            ->sender($account->getAddress())
            ->suggestedFeePerByte($feePerByte->toInt())
            ->firstValid($transaction->firstValid->toInt())
            ->lastValid($transaction->lastValid->toInt())
            ->genesisHash($transaction->genesisHash)
            ->amount($transaction->getFee()->toInt())
            ->receiver($transaction->sender)
            ->build();

        $reimbursement->lease = $transaction->lease;
        $reimbursement->setFeeByFeePerByte($feePerByte);

        AtomicTransfer::group([$reimbursement, $transaction]);

        $signedTx = SignedTransaction::fromLogicSignature($transaction, $signature);
        $signedReimbursementTx = $reimbursement->sign($account);

        $encoded1 = Encoder::getInstance()->encodeMessagePack($signedReimbursementTx->toMessagePack());
        $encoded2 = Encoder::getInstance()->encodeMessagePack($signedTx->toMessagePack());

        return $encoded1 . $encoded2;
    }
}
