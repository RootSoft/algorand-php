<?php
require_once('../../vendor/autoload.php');

use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\AlgoExplorer;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Clients\KmdClient;
use Rootsoft\Algorand\KMD\Model\CreateWalletRequest;
use Rootsoft\Algorand\Models\Accounts\Account;

class KmdExample
{
    public static function main()
    {
        // Create the clients
        $algodClient = new AlgodClient(AlgoExplorer::TESTNET_ALGOD_API_URL);
        $indexerClient = new IndexerClient(AlgoExplorer::TESTNET_INDEXER_API_URL);
        $kmdClient = new KmdClient('192.168.66.247:7833', '');
        $algorand = new Algorand($algodClient, $indexerClient, $kmdClient);

        // Import your account
        $account = Account::mnemonic('year crumble opinion local grid injury rug happy away castle minimum bitter upon romance federal entire rookie net fabric soft comic trouble business above talent');

        // Create a new wallet
        //self::createWallet($algorand, $account);

        // Get the versions
        //self::versions($algorand);

        // Get swagger
        //self::swagger($algorand);

        // List wallets
        self::listWallets($algorand);
    }

    public static function createWallet(Algorand $algorand, Account $account)
    {
       $request = new CreateWalletRequest([
           "wallet_name" => "test1",
           "wallet_password" => "test",
           "wallet_driver_name" => "sqlite",
       ]);

        try {
            $result = $algorand->kmd()->createWallet($request);
            print_r($result);
        } catch (Exception $e) {
            echo 'Exception when calling DefaultApi->createWallet: ', $e->getMessage(), PHP_EOL;
        }
    }

    public static function versions(Algorand $algorand)
    {
        try {
            $result = $algorand->kmd()->getVersion();
            print_r($result);
        } catch (Exception $e) {
            echo 'Exception when calling DefaultApi->getVersion(): ', $e->getMessage(), PHP_EOL;
        }
    }

    public static function swagger(Algorand $algorand)
    {
        try {
            $result = $algorand->kmd()->swaggerHandler();
            print_r($result);
        } catch (Exception $e) {
            echo 'Exception when calling DefaultApi->getVersion(): ', $e->getMessage(), PHP_EOL;
        }
    }

    public static function listWallets(Algorand $algorand)
    {
        try {
            $result = $algorand->kmd()->listWallets();
            print_r($result);
        } catch (Exception $e) {
            echo 'Exception when calling DefaultApi->getVersion(): ', $e->getMessage(), PHP_EOL;
        }
    }
}

KmdExample::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}
