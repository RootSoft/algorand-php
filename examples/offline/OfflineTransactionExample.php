<?php
require_once('../../vendor/autoload.php');

use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\AlgoExplorer;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;

class OfflineTransactionExample
{
    public static function main()
    {
        $algodClient = new AlgodClient(AlgoExplorer::TESTNET_ALGOD_API_URL);
        $indexerClient = new IndexerClient(AlgoExplorer::TESTNET_INDEXER_API_URL);

        $algorand = new Algorand($algodClient, $indexerClient);

        $account = Account::mnemonic('year crumble opinion local grid injury rug happy away castle minimum bitter upon romance federal entire rookie net fabric soft comic trouble business above talent');

        prettyPrint("Account: " . $account->getPublicAddress());

        // Export the transaction
        self::exportTransaction($algorand, $account, 'signed.txn');

        // TODO Import the transaction

        // TODO Broadcast the imported transaction
    }

    public static function exportTransaction(Algorand $algorand, Account $account, string $fileName)
    {
        $receiver = Address::fromAlgorandAddress('L5EUPCF4ROKNZMAE37R5FY2T5DF2M3NVYLPKSGWTUKVJRUGIW4RKVPNPD4');

        // Get the suggested parameters
        $params = $algorand->getSuggestedTransactionParams();

        // Create the transaction
        $transaction = TransactionBuilder::payment()
            ->sender($account->getAddress())
            ->receiver($receiver)
            ->noteText('Hello world')
            ->amount(1000) // 0.001 Algo
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTx = $transaction->sign($account);

        // Export the transaction
        $signedTx->export($fileName);
    }

    public static function importTransaction()
    {
        // TODO Implement messagepack decoding
    }
}

OfflineTransactionExample::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}
