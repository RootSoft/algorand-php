<?php

namespace Rootsoft\Algorand\Templates;

use Brick\Math\BigInteger;
use http\Exception\InvalidArgumentException;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\AtomicTransfer;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\SignedTransaction;
use Rootsoft\Algorand\Templates\Parameters\AddressParameterValue;
use Rootsoft\Algorand\Templates\Parameters\IntParameterValue;
use Rootsoft\Algorand\Utils\Buffer;
use Rootsoft\Algorand\Utils\Encoder;

/**
 * A limit order allows a user to exchange some number of assets for some number of algos.
 * Fund the contract with some number of Algos to limit the maximum number of Algos you're willing to trade for some
 * other asset.
 * Works on two cases:
 * - trading Algos for some other asset
 * - closing out Algos back to the originator after a timeout
 *
 */
class LimitOrder
{
    const REFERENCE_PROGRAM = 'ASAKAAEFAgYEBwgJCiYBIP68oLsUSlpOp7Q4pGgayA5soQW8tgf8VlMlyVaV9qITMRYiEjEQIxIQMQEkDhAyBCMSQABVMgQlEjEIIQQNEDEJMgMSEDMBECEFEhAzAREhBhIQMwEUKBIQMwETMgMSEDMBEiEHHTUCNQExCCEIHTUENQM0ATQDDUAAJDQBNAMSNAI0BA8QQAAWADEJKBIxAiEJDRAxBzIDEhAxCCISEBA=';

    /**
     * Create a new limit order contract.
     * A limit order allows a user to exchange some number of assets for some number of algos.
     *
     * @param Address $owner
     * @param int $assetId
     * @param int $ratn
     * @param int $ratd
     * @param int $expirationRound
     * @param int $minTrade
     * @param int $maxFee
     * @return ContractTemplate
     */
    public static function create(
        Address $owner,
        int $assetId,
        int $ratn,
        int $ratd,
        int $expirationRound,
        int $minTrade,
        int $maxFee
    ): ContractTemplate {
        $values = [
            new IntParameterValue(5, $maxFee),
            new IntParameterValue(7, $minTrade),
            new IntParameterValue(9, $assetId),
            new IntParameterValue(10, $ratd),
            new IntParameterValue(11, $ratn),
            new IntParameterValue(12, $expirationRound),
            new AddressParameterValue(16, $owner),
        ];

        return ContractTemplate::inject(Base64::decode(self::REFERENCE_PROGRAM), $values);
    }

    /**
     * Creates a group transaction array which transfer funds according to the contract's ratio
     *
     * @param ContractTemplate $contract
     * @param Account $sender
     * @param int $assetAmount
     * @param int $microAlgoAmount
     * @param int $firstValid
     * @param int $lastValid
     * @param string $genesisHash
     * @param int $feePerByte
     * @return string
     * @throws \Rootsoft\Algorand\Exceptions\AlgorandException
     * @throws \SodiumException
     */
    public static function getSwapAssetsTransaction(
        ContractTemplate $contract,
        Account $sender,
        int $assetAmount,
        int $microAlgoAmount,
        int $firstValid,
        int $lastValid,
        string $genesisHash,
        int $feePerByte
    ) : string {
        $data = ContractTemplate::readAndVerifyContract($contract->getProgram(), 10, 1);

        $owner = Address::fromPublicKey(Buffer::toBinaryString($data->byteBlock[0]));
        $maxFee = $data->intBlock[2];
        $minTrade = $data->intBlock[4];
        $assetId = $data->intBlock[6];
        $ratd = $data->intBlock[7];
        $ratn = $data->intBlock[8];

        if ($assetAmount * $ratd != $microAlgoAmount * $ratn) {
            throw new InvalidArgumentException('The exchange ratio of assets to microalgos must be exactly');
        }

        if ($microAlgoAmount < $minTrade) {
            throw new InvalidArgumentException("At least $minTrade microalgos must be requested.");
        }

        $tx1 = TransactionBuilder::payment()
            ->sender($contract->getAddress())
            ->suggestedFeePerByte($feePerByte)
            ->firstValid($firstValid)
            ->lastValid($lastValid)
            ->genesisHashB64($genesisHash)
            ->amount($microAlgoAmount)
            ->receiver($sender->getAddress())
            ->build();

        $tx2 = TransactionBuilder::assetTransfer()
            ->sender($sender->getAddress())
            ->assetReceiver($owner)
            ->amount($assetAmount)
            ->suggestedFeePerByte($feePerByte)
            ->firstValid($firstValid)
            ->lastValid($lastValid)
            ->genesisHashB64($genesisHash)
            ->assetId(BigInteger::of($assetId))
            ->build();

        if ($tx1->getFee()->toInt() > $maxFee || $tx2->getFee()->toInt() > $maxFee) {
            throw new InvalidArgumentException("Transaction fee is greater than maxFee");
        }

        AtomicTransfer::group([$tx1, $tx2]);

        $lsig = new LogicSignature($contract->getProgram());
        $signedTx1 = SignedTransaction::fromLogicSignature($tx1, $lsig);
        $signedTx2 = $tx2->sign($sender);

        $encoded1 = Encoder::getInstance()->encodeMessagePack($signedTx1->toMessagePack());
        $encoded2 = Encoder::getInstance()->encodeMessagePack($signedTx2->toMessagePack());
        return $encoded1 . $encoded2;
    }
}
