<?php
require_once('../../vendor/autoload.php');

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\AlgoExplorer;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Applications\StateSchema;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Utils\AlgorandUtils;

class StatefulSmartContractsExample
{
    public static function main()
    {
        $algodClient = new AlgodClient(AlgoExplorer::TESTNET_ALGOD_API_URL);
        $indexerClient = new IndexerClient(AlgoExplorer::TESTNET_INDEXER_API_URL);

        $algorand = new Algorand($algodClient, $indexerClient);

        $account = Account::mnemonic('year crumble opinion local grid injury rug happy away castle minimum bitter upon romance federal entire rookie net fabric soft comic trouble business above talent');
        $manager = Account::mnemonic('year crumble opinion local grid injury rug happy away castle minimum bitter upon romance federal entire rookie net fabric soft comic trouble business above talent');

        // Create a new stateful smart contract
        //self::createApplication($algorand, $account);

        // Opt in to application
        //self::optIn($algorand, $account);

        // Call the application
        //self::callContract($algorand, $account);

        // Update the application
        //self::updateContract($algorand, $account);

        // Delete the application
        //self::deleteContract($algorand, $account);

        // Close out the application
        //self::closeOut($algorand, $account);

        // Clearing state
        //self::clearState($algorand, $account);
    }

    public static function createApplication(Algorand $algorand, Account $sender)
    {
        prettyPrint('Creating a new application');

        // Compile TEAL programs
        $approvalProgram = $algorand->applicationManager()->compileTEAL(self::$approvalProgramSource);
        $clearProgram = $algorand->applicationManager()->compileTEAL(self::$clearProgramSource);

        // Declare schema
        $localInts = 1;
        $localBytes = 1;
        $globalInts = 1;
        $globalBytes = 0;

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Create the application transaction
        $transaction = TransactionBuilder::applicationCreate()
            ->sender($sender->getAddress())
            ->approvalProgram($approvalProgram->program())
            ->clearStateProgram($clearProgram->program())
            ->globalStateSchema(new StateSchema($globalInts, $globalBytes))
            ->localStateSchema(new StateSchema($localInts, $localBytes))
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($sender);

        // Broadcast the transaction on the network
        $txId = $algorand->sendTransaction($signedTransaction);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Application created in round ' . $response->confirmedRound);
    }

    public static function optIn(Algorand $algorand, Account $account)
    {
        $applicationId = 25492589;
        prettyPrint('Opting in to the application: ' . $applicationId);

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Create the opt in transaction
        $transaction = TransactionBuilder::applicationOptIn()
            ->sender($account->getAddress())
            ->applicationId(BigInteger::of($applicationId))
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        $txId = $algorand->sendTransaction($signedTransaction);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Opted in to application in round ' . $response->confirmedRound);
    }

    public static function callContract(Algorand $algorand, Account $account)
    {
        $applicationId = 25492589;
        prettyPrint('Calling application: ' . $applicationId);

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Specify the arguments
        $arguments = AlgorandUtils::parse_application_arguments('str:arg1,int:12');

        // Create the call transaction
        $transaction = TransactionBuilder::applicationCall()
            ->sender($account->getAddress())
            ->applicationId(BigInteger::of($applicationId))
            ->arguments($arguments)
            ->accounts([$account->getAddress()])
            ->foreignApps([22240890])
            ->foreignAssets([408947])
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        $txId = $algorand->sendTransaction($signedTransaction);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Called application in round ' . $response->confirmedRound);
    }

    public static function updateContract(Algorand $algorand, Account $account)
    {
        $applicationId = 25492589;
        prettyPrint('Updating application: ' . $applicationId);

        // Compile TEAL programs
        $approvalProgram = $algorand->applicationManager()->compileTEAL(self::$approvalProgramSource);
        $clearProgram = $algorand->applicationManager()->compileTEAL(self::$clearProgramSource);

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Create the call transaction
        $transaction = TransactionBuilder::applicationUpdate()
            ->sender($account->getAddress())
            ->applicationId(BigInteger::of($applicationId))
            ->approvalProgram($approvalProgram->program())
            ->clearStateProgram($clearProgram->program())
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        $txId = $algorand->sendTransaction($signedTransaction);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Application updated in round ' . $response->confirmedRound);
    }

    public static function deleteContract(Algorand $algorand, Account $account)
    {
        $applicationId = 25492589;
        prettyPrint('Deleting application: ' . $applicationId);

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Create the delete transaction
        $transaction = TransactionBuilder::applicationDelete()
            ->sender($account->getAddress())
            ->applicationId(BigInteger::of($applicationId))
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        $txId = $algorand->sendTransaction($signedTransaction);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Application deleted in round ' . $response->confirmedRound);
    }

    public static function closeOut(Algorand $algorand, Account $account)
    {
        $applicationId = 25492589;
        prettyPrint('Closing out application: ' . $applicationId);

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Create the close out transaction
        $transaction = TransactionBuilder::applicationCloseOut()
            ->sender($account->getAddress())
            ->applicationId(BigInteger::of($applicationId))
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        $txId = $algorand->sendTransaction($signedTransaction);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Closed out in round ' . $response->confirmedRound);
    }

    public static function clearState(Algorand $algorand, Account $account)
    {
        $applicationId = 25492589;
        prettyPrint('Clearing state application: ' . $applicationId);

        // Get the suggested transaction params
        $params = $algorand->getSuggestedTransactionParams();

        // Create the close out transaction
        $transaction = TransactionBuilder::applicationClearState()
            ->sender($account->getAddress())
            ->applicationId(BigInteger::of($applicationId))
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        $txId = $algorand->sendTransaction($signedTransaction);
        prettyPrint('Transaction id: ' . $txId);
        prettyPrint('Waiting for confirmation');
        $response = $algorand->waitForConfirmation($txId);
        prettyPrint('Cleared state in round ' . $response->confirmedRound);
    }

    private static $approvalProgramSource = '#pragma version 4
        
        // read global state
        byte "counter"
        dup
        app_global_get
        
        // increment the value
        int 1
        +
        
        // store to scratch space
        dup
        store 0
        
        // update global state
        app_global_put
        
        // load return value as approval
        load 0
        return';

    private static $clearProgramSource = '#pragma version 4
        int 1';
}

StatefulSmartContractsExample::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}
