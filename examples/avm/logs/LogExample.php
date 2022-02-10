<?php

require_once '../../../vendor/autoload.php';

use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\AlgoExplorer;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Applications\OnCompletion;
use Rootsoft\Algorand\Models\Applications\StateSchema;
use Rootsoft\Algorand\Models\Transactions\AtomicTransfer;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\Types\ApplicationBaseTransaction;

class LogExample
{
    public static function main()
    {
        $algodClient = new AlgodClient(AlgoExplorer::TESTNET_ALGOD_API_URL);
        $indexerClient = new IndexerClient(AlgoExplorer::TESTNET_INDEXER_API_URL);
        $algorand = new Algorand($algodClient, $indexerClient);

        // Get the account
        $account = self::getAccount();
        prettyPrint('Account: ' . $account->getPublicAddress());

        // Deploy the application
        $applicationId = 70293890; //self::createApp($algorand, $account);
        prettyPrint('Application id: ' . $applicationId);

        // Get the application address
        $applicationAddress = Address::forApplication($applicationId);
        prettyPrint('Application address: ' . $applicationAddress->encodedAddress);

        // Group the transaction
        $groupedTxs = AtomicTransfer::group(
            [
                self::getApplicationCallTx($algorand, $account, $applicationId),
            ]
        );

        // Sign the transactions
        $signedTxs = [];
        foreach ($groupedTxs as $tx) {
            $signedTxs[] = $tx->sign($account);
        }

        // Broadcast the transaction
        $pendingTx = $algorand->sendTransactions($signedTxs, true);
        prettyPrint('Result confirmed in round address: ' . $pendingTx->confirmedRound);
        prettyPrint('Logs:');
        print_r($pendingTx->logs);
    }

    public static function getAccount()
    {
        return Account::mnemonic('note goddess slot wire globe hurdle quote lawn session denial ozone mansion obey woman wonder slogan warfare hero federal caught match toy device about water');
    }

    /**
     * @param Algorand $algorand
     * @param Account $account
     * @return int
     * @throws SodiumException
     * @throws AlgorandException
     */
    public static function createApp(Algorand $algorand, Account $account): int
    {
        // Get the suggested tx params
        $params = $algorand->getSuggestedTransactionParams();

        // Read & compile approval program
        $approvalSourceCode = file_get_contents(__DIR__ . '/approval.teal', 'r');
        $approvalProgram = $algorand->applicationManager()->compileTEAL($approvalSourceCode);

        // Read & compile clear state program
        $clearStateSourceCode = file_get_contents(__DIR__ . '/clear.teal', 'r');
        $clearStateProgram = $algorand->applicationManager()->compileTEAL($clearStateSourceCode);

        // Create the application tx
        $appTx = TransactionBuilder::applicationCreate()
            ->sender($account->getAddress())
            ->approvalProgram($approvalProgram->program())
            ->clearStateProgram($clearStateProgram->program())
            ->globalStateSchema(new StateSchema(0, 0))
            ->localStateSchema(new StateSchema(0, 0))
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTx = $appTx->sign($account);

        // Broadcast the transaction
        $pendingTx = $algorand->sendTransaction($signedTx, true);

        $applicationIndex = $pendingTx->applicationIndex;

        if ($applicationIndex == null) {
            throw new AlgorandException('No application index');
        }

        return $applicationIndex;
    }

    /**
     * @param Algorand $algorand
     * @param Account $sender
     * @param Address $receiver
     * @param int $amount
     * @return ApplicationBaseTransaction
     * @throws AlgorandException
     */
    public static function getApplicationCallTx(Algorand $algorand, Account $sender, int $applicationId): ApplicationBaseTransaction
    {
        return TransactionBuilder::applicationCall()
            ->sender($sender->getAddress())
            ->applicationId($applicationId)
            ->onCompletion(OnCompletion::NO_OP_OC())
            ->useSuggestedParams($algorand)
            ->build();
    }
}

LogExample::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}