<?php
require_once('../../vendor/autoload.php');

use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\AlgoExplorer;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;

class YourFirstTransactionExample
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

        // Specify the receiver
        $receiver = Address::fromAlgorandAddress('KTFZ5SQU3AQ6UFYI2QOWF5X5XJTAFRHACWHXAZV6CPLNKS2KSGQWPT4ACE');

        // Create a payment transaction
        $transaction = TransactionBuilder::payment()
            ->sender($account->getAddress())
            ->note('Your first transaction')
            ->amount(1000)
            ->receiver($receiver)
            ->useSuggestedParams($algorand)
            ->build();

        // Sign the transaction
        $signedTx = $transaction->sign($account);

        // Submit the transaction to the network
        $txId = $algorand->sendTransaction($signedTx);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Transaction confirmed in round ' . $response->confirmedRound);
    }
}

YourFirstTransactionExample::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}
