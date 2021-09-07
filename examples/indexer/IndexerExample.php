<?php
require_once('../../vendor/autoload.php');

use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\AlgoExplorer;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Addresses\AddressRole;
use Rootsoft\Algorand\Models\Transactions\TransactionType;

class IndexerExample
{
    public static function main()
    {
        // Create the clients
        $algodClient = new AlgodClient(AlgoExplorer::TESTNET_ALGOD_API_URL);
        $indexerClient = new IndexerClient(AlgoExplorer::TESTNET_INDEXER_API_URL);
        $algorand = new Algorand($algodClient, $indexerClient);

        // Import your account
        $account = Account::mnemonic('year crumble opinion local grid injury rug happy away castle minimum bitter upon romance federal entire rookie net fabric soft comic trouble business above talent');
        prettyPrint("Account: " . $account->getPublicAddress());

        // Get the account information
        self::getAccountInformation($algorand, $account);
    }

    public static function getAccountInformation(Algorand $algorand, Account $account)
    {
        $information = $algorand->accountManager()->getAccountInformation($account->getPublicAddress());
        prettyPrint($information->address);
    }

    public static function findAssetById(Algorand $algorand, Account $account)
    {
        $response = $algorand->assetManager()->getAssetById(408947);
        prettyPrint(json_encode($response));
    }

    public static function findAssetMinBalances(Algorand $algorand, Account $account)
    {
        $response = $algorand
            ->indexer()
            ->assets()
            ->whereAssetId(408947)
            ->whereCurrencyIsGreaterThan(3007326000)
            ->search();

        prettyPrint(json_encode($response));
    }

    public static function findAssetBalances(Algorand $algorand, Account $account)
    {
        $response = $algorand
            ->indexer()
            ->accounts()
            ->balances(440307)
            ->search();

        prettyPrint(json_encode($response));
    }

    public static function findAssetsMinBalance(Algorand $algorand, Account $account)
    {
        $response = $algorand
            ->indexer()
            ->accounts()
            ->balances(440307)
            ->whereCurrencyIsGreaterThan(0)
            ->search();

        prettyPrint(json_encode($response));
    }

    public static function getBlockInfo(Algorand $algorand, Account $account)
    {
        $response = $algorand
            ->getBlock(16280357);

        prettyPrint(json_encode($response));
    }

    public static function findApplicationById(Algorand $algorand, Account $account)
    {
        $response = $algorand->applicationManager()->getApplicationById(15974179);

        prettyPrint(json_encode($response));
    }

    public static function searchApplications(Algorand $algorand, Account $account)
    {
        $response = $algorand->indexer()->applications()->search(4);

        prettyPrint(json_encode($response));
    }

    public static function searchAssets(Algorand $algorand, Account $account)
    {
        $response = $algorand->indexer()->assets()->search(4);

        prettyPrint(json_encode($response));
    }

    public static function searchAssetsByName(Algorand $algorand, Account $account)
    {
        $response = $algorand->indexer()->assets()->whereAssetName('Mario')->search(4);

        prettyPrint(json_encode($response));
    }

    public static function searchAssetsWithRole(Algorand $algorand, Account $account)
    {
        $address = Address::fromAlgorandAddress('G26NNWKJUPSTGVLLDHCUQ7LFJHMZP2UUAQG2HURLI6LOEI235YCQUNPQEI');
        $response = $algorand->indexer()
            ->transactions()
            ->whereAddress($address)
            ->whereAssetId(408947)
            ->whereAddressRole(AddressRole::RECEIVER())
            ->search(4);

        prettyPrint(json_encode($response));
    }

    public static function searchTransactions(Algorand $algorand, Account $account)
    {
        $address = Address::fromAlgorandAddress('G26NNWKJUPSTGVLLDHCUQ7LFJHMZP2UUAQG2HURLI6LOEI235YCQUNPQEI');
        $response = $algorand->indexer()
            ->transactions()
            ->whereCurrencyIsGreaterThan(0)
            ->search(4);

        prettyPrint(json_encode($response));
    }

    public static function searchTransactionsWithNote(Algorand $algorand, Account $account)
    {
        $response = $algorand->indexer()
            ->transactions()
            ->whereNotePrefix('showing prefix')
            ->search(4);

        prettyPrint(json_encode($response));
    }

    public static function searchTransactionsPaging(Algorand $algorand, Account $account)
    {
        $numtx = 1;
        $nextToken = '';

        while ($numtx > 0) {
            $minAmount = 500000000000;
            $limit = 4;
            $nextPage = $nextToken;

            $response = $algorand->indexer()
                ->transactions()
                ->next($nextPage)
                ->whereCurrencyIsGreaterThan($minAmount)
                ->beforeMaxRound(30000)
                ->search($limit);

            $numtx = count($response->transactions);
            if ($numtx > 0) {
                $nextToken = $response->nextToken;
            }

            prettyPrint(json_encode($response));
        }

    }

    public static function searchTxAddressAsset(Algorand $algorand, Account $account)
    {
        $address = Address::fromAlgorandAddress('AMF3CVE4MFZM24CCFEWRCOCWW7TEDJQS3O26OUBRHZ3KWKUBE5ZJRNZ3OY');
        $response = $algorand->indexer()
            ->transactions()
            ->whereAddress($address)
            ->whereCurrencyIsGreaterThan(5)
            ->whereAssetId(12215366)
            ->whereNotePrefix('showing prefix')
            ->search(2);

        prettyPrint(json_encode($response));
    }

    public static function searchTransactionsInBlock(Algorand $algorand, Account $account)
    {
        $address = Address::fromAlgorandAddress('NI2EDLP2KZYH6XYLCEZSI5SSO2TFBYY3ZQ5YQENYAGJFGXN4AFHPTR3LXU');
        $response = $algorand->indexer()
            ->transactions()
            ->whereAddress($address)
            ->whereRound(8965633)
            ->search(2);

        prettyPrint(json_encode($response));
    }

    public static function searchTransactionsBeforeTime(Algorand $algorand, Account $account)
    {
        $address = Address::fromAlgorandAddress('NI2EDLP2KZYH6XYLCEZSI5SSO2TFBYY3ZQ5YQENYAGJFGXN4AFHPTR3LXU');
        $response = $algorand->indexer()
            ->transactions()
            ->whereAddress($address)
            ->before(new DateTime())
            ->search(2);

        prettyPrint(json_encode($response));
    }

    public static function searchTransactionsWithType(Algorand $algorand, Account $account)
    {
        $response = $algorand->indexer()
            ->transactions()
            ->whereTransactionType(TransactionType::PAYMENT())
            ->search(2);

        prettyPrint(json_encode($response));
    }
}

IndexerExample::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}
