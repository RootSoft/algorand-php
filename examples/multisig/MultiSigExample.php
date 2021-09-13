<?php

require_once '../../vendor/autoload.php';

use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\AlgoExplorer;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Crypto\Ed25519PublicKey;
use Rootsoft\Algorand\Crypto\MultiSignatureAddress;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;

class MultiSigExample
{
    public static function main()
    {
        $algodClient = new AlgodClient(AlgoExplorer::TESTNET_ALGOD_API_URL);
        $indexerClient = new IndexerClient(AlgoExplorer::TESTNET_INDEXER_API_URL);

        $algorand = new Algorand($algodClient, $indexerClient);

        $accountA = Account::mnemonic('year crumble opinion local grid injury rug happy away castle minimum bitter upon romance federal entire rookie net fabric soft comic trouble business above talent');
        $accountB = Account::mnemonic('beauty nurse season autumn curve slice cry strategy frozen spy panic hobby strong goose employ review love fee pride enlist friend enroll clip ability runway');
        $accountC = Account::mnemonic('picnic bright know ticket purity pluck stumble destroy ugly tuna luggage quote frame loan wealth edge carpet drift cinnamon resemble shrimp grain dynamic absorb edge');

        prettyPrint('Account 1: ' . $accountA->getPublicAddress());
        prettyPrint('Account 2: ' . $accountB->getPublicAddress());
        prettyPrint('Account 3: ' . $accountC->getPublicAddress());

        $publicKeys = array_map(fn (Account $value) => new Ed25519PublicKey($value->getPublicKey()), [$accountA, $accountB, $accountC]);
        $msa = new MultiSignatureAddress(1, 2, $publicKeys);
        prettyPrint('Multisig Address: ' . $msa->toAddress()->encodedAddress);

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Create the transaction
        $transaction = TransactionBuilder::payment()
            ->sender($msa->toAddress())
            ->noteText('Multisig example')
            ->amount(1000) // 0.001 Algo
            ->receiver($accountC->getAddress())
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTx = $msa->sign($accountA, $transaction);
        $completeTx = $msa->append($accountB, $signedTx);

        // Broadcast the transaction
        $txId = $algorand->sendTransaction($completeTx);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Multisig transaction confirmed in round ' . $response->confirmedRound);
    }
}

MultiSigExample::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}
