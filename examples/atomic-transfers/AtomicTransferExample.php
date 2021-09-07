<?php
require_once('../../vendor/autoload.php');

use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\AlgoExplorer;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Transactions\AtomicTransfer;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;

class AtomicTransferExample
{
    public static function main()
    {
        $algodClient = new AlgodClient(AlgoExplorer::TESTNET_ALGOD_API_URL);
        $indexerClient = new IndexerClient(AlgoExplorer::TESTNET_INDEXER_API_URL);

        $algorand = new Algorand($algodClient, $indexerClient);

        $accountA = Account::mnemonic('year crumble opinion local grid injury rug happy away castle minimum bitter upon romance federal entire rookie net fabric soft comic trouble business above talent');
        $accountB = Account::mnemonic('beauty nurse season autumn curve slice cry strategy frozen spy panic hobby strong goose employ review love fee pride enlist friend enroll clip ability runway');
        $accountC = Account::mnemonic('picnic bright know ticket purity pluck stumble destroy ugly tuna luggage quote frame loan wealth edge carpet drift cinnamon resemble shrimp grain dynamic absorb edge');

        prettyPrint("Account 1: " . $accountA->getPublicAddress());
        prettyPrint("Account 2: " . $accountB->getPublicAddress());
        prettyPrint("Account 3: " . $accountC->getPublicAddress());

        // Get the suggested transaction parameters
        $params = $algorand->getSuggestedTransactionParams();

        // Create the first transaction
        $transaction1 = TransactionBuilder::payment()
            ->sender($accountA->getAddress())
            ->noteText('Atomic transfer from account A to account B')
            ->amount(1000) // 0.001 Algo
            ->receiver($accountB->getAddress())
            ->suggestedParams($params)
            ->build();

        // Create the second transaction
        $transaction2 = TransactionBuilder::payment()
            ->sender($accountB->getAddress())
            ->noteText('Atomic transfer from account B to account A')
            ->amount(2000) // 0.002 Algo
            ->receiver($accountA->getAddress())
            ->useSuggestedParams($algorand)
            ->build();

        // Combine the transactions and calculate the group id
        AtomicTransfer::group([$transaction1, $transaction2]);

        // Sign the transactions
        $signedTransaction1 = $transaction1->sign($accountA);
        $signedTransaction2 = $transaction2->sign($accountB);

        // Broadcast the transaction
        $txId = $algorand->sendTransactions([$signedTransaction1, $signedTransaction2]);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Atomic transaction confirmed in round ' . $response->confirmedRound);
    }
}

AtomicTransferExample::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}
