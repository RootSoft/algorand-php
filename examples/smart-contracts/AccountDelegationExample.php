<?php
require_once('../../vendor/autoload.php');

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\AlgoExplorer;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;

class AccountDelegationExample
{
    public static function main()
    {
        $algodClient = new AlgodClient(AlgoExplorer::TESTNET_ALGOD_API_URL);
        $indexerClient = new IndexerClient(AlgoExplorer::TESTNET_INDEXER_API_URL);
        $algorand = new Algorand($algodClient, $indexerClient);

        $account = Account::mnemonic('year crumble opinion local grid injury rug happy away castle minimum bitter upon romance federal entire rookie net fabric soft comic trouble business above talent');
        prettyPrint("Account: " . $account->getPublicAddress());

        $arguments = [BigInteger::of(123)->toBytes()];

        $result = $algorand->applicationManager()->compileTEAL(self::$sampleArgsTeal);
        $lsig = LogicSignature::fromProgram($result->program(), $arguments)->sign($account);
        $receiver = 'KTFZ5SQU3AQ6UFYI2QOWF5X5XJTAFRHACWHXAZV6CPLNKS2KSGQWPT4ACE';

        $transaction = TransactionBuilder::payment()
            ->sender($account->getAddress())
            ->note('Account delegation')
            ->amount(1000)
            ->receiver(Address::fromAlgorandAddress($receiver))
            ->useSuggestedParams($algorand)
            ->build();

        // Sign the logic transaction
        $signedTx = $lsig->signTransaction($transaction);

        // Send the transaction
        $txId = $algorand->sendTransaction($signedTx);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Account delegation confirmed in round ' . $response->confirmedRound);
    }

    private static $sampleArgsTeal = '// samplearg.teal
        // This code is meant for learning purposes only
        // It should not be used in production
        arg_0
        btoi
        int 123
        ==';
}

AccountDelegationExample::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}
