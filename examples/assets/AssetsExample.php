<?php

require_once '../../vendor/autoload.php';

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\AlgoExplorer;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;

class AssetsExample
{
    public static function main()
    {
        $algodClient = new AlgodClient(AlgoExplorer::TESTNET_ALGOD_API_URL);
        $indexerClient = new IndexerClient(AlgoExplorer::TESTNET_INDEXER_API_URL);

        $algorand = new Algorand($algodClient, $indexerClient);

        $account = Account::mnemonic('year crumble opinion local grid injury rug happy away castle minimum bitter upon romance federal entire rookie net fabric soft comic trouble business above talent');
        $manager = Account::mnemonic('year crumble opinion local grid injury rug happy away castle minimum bitter upon romance federal entire rookie net fabric soft comic trouble business above talent');

        // Create a new asset
        //self::createAsset($algorand, $account, $manager);

        // Change manager address
        //self::changeManagerAddress($algorand, $account, $manager);

        // Opt in
        //self::optIn($algorand, $account, $manager);

        // Transfer asset
        //self::transferAssets($algorand, $account, $manager);

        // Freeze asset
        //self::freezeAsset($algorand, $account, $manager);

        // Revoke assets
        //self::revokeAsset($algorand, $account, $manager);
    }

    public static function createAsset(Algorand $algorand, Account $sender, Account $manager)
    {
        prettyPrint('Creating a new asset');

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Create a new asset (e.g. 25489201)
        $transaction = TransactionBuilder::assetConfig()
            ->assetName('my longer asset name')
            ->unitName('myunit')
            ->totalAssetsToCreate(BigInteger::of(10000))
            ->decimals(0)
            ->defaultFrozen(false)
            ->managerAddress($manager->getAddress())
            ->reserveAddress($manager->getAddress())
            ->freezeAddress($manager->getAddress())
            ->clawbackAddress($manager->getAddress())
            ->sender($sender->getAddress())
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($sender);

        // Broadcast the transaction on the network
        $txId = $algorand->sendTransaction($signedTransaction);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Asset created in round ' . $response->confirmedRound);
    }

    public static function changeManagerAddress(Algorand $algorand, Account $sender, Account $manager)
    {
        prettyPrint('Change manager address');

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Create config transaction with new manager address
        $transaction = TransactionBuilder::assetConfig()
            ->sender($sender->getAddress())
            ->assetId(BigInteger::of(25489201))
            ->managerAddress($sender->getAddress())
            ->reserveAddress($manager->getAddress())
            ->freezeAddress($manager->getAddress())
            ->clawbackAddress($manager->getAddress())
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($sender);

        // Broadcast the transaction on the network
        $txId = $algorand->sendTransaction($signedTransaction);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Manager changed in round ' . $response->confirmedRound);
    }

    public static function optIn(Algorand $algorand, Account $sender, Account $manager)
    {
        prettyPrint('Opt in to asset');

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Create opt in transaction
        $transaction = TransactionBuilder::assetTransfer()
            ->assetId(BigInteger::of(25489201))
            ->sender($sender->getAddress())
            ->assetReceiver($sender->getAddress())
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($sender);

        // Broadcast the transaction on the network
        $txId = $algorand->sendTransaction($signedTransaction);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Opted in in round ' . $response->confirmedRound);
    }

    public static function transferAssets(Algorand $algorand, Account $sender, Account $receiver)
    {
        prettyPrint('Transferring asset');

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Create asset transfer transaction
        $transaction = TransactionBuilder::assetTransfer()
            ->assetId(BigInteger::of(25489201))
            ->sender($sender->getAddress())
            ->assetReceiver($receiver->getAddress())
            ->amount(10)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($sender);

        // Broadcast the transaction on the network
        $txId = $algorand->sendTransaction($signedTransaction);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Asset transferred in round ' . $response->confirmedRound);
    }

    public static function destroyAsset(Algorand $algorand, Account $sender, Account $receiver)
    {
        prettyPrint('Destroying asset');

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Create asset destroy transaction
        $transaction = TransactionBuilder::assetConfig()
            ->assetId(BigInteger::of(1))
            ->sender($sender->getAddress())
            ->suggestedParams($params)
            ->destroy();

        // Sign the transaction
        $signedTransaction = $transaction->sign($sender);

        // Broadcast the transaction on the network
        $txId = $algorand->sendTransaction($signedTransaction);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Asset destroyed in round ' . $response->confirmedRound);
    }

    public static function freezeAsset(Algorand $algorand, Account $sender, Account $receiver)
    {
        prettyPrint('Freezing asset');

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Create asset freeze transaction
        $transaction = TransactionBuilder::assetFreeze()
            ->assetId(BigInteger::of(25489201))
            ->freezeTarget($receiver->getAddress())
            ->freeze(false)
            ->sender($sender->getAddress())
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($sender);

        // Broadcast the transaction on the network
        $txId = $algorand->sendTransaction($signedTransaction);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Asset frozen in round ' . $response->confirmedRound);
    }

    public static function revokeAsset(Algorand $algorand, Account $sender, Account $receiver)
    {
        prettyPrint('Revoking asset');

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Revoke the assets
        $transaction = TransactionBuilder::assetTransfer()
            ->assetId(BigInteger::of(25489201))
            ->amount(1)
            ->assetSender($receiver->getAddress()) // address of the account from which the assets will be revoked
            ->assetReceiver($sender->getAddress()) // Address which receives the revoked assets
            ->sender($sender->getAddress())
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($sender);

        // Broadcast the transaction on the network
        $txId = $algorand->sendTransaction($signedTransaction);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Asset revoked in round ' . $response->confirmedRound);
    }
}

AssetsExample::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}
