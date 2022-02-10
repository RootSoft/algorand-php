<?php

require_once '../../../vendor/autoload.php';

use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\AlgoExplorer;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Applications\StateSchema;
use Rootsoft\Algorand\Models\Transactions\AtomicTransfer;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\Types\ApplicationBaseTransaction;
use Rootsoft\Algorand\Utils\AlgorandUtils;

class OpPoolExample
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
        $applicationId = 70297177; //self::createApp($algorand, $account);
        prettyPrint('Application id: ' . $applicationId);

        // Get the application address
        $applicationAddress = Address::forApplication($applicationId);
        prettyPrint('Application address: ' . $applicationAddress->encodedAddress);

        //self::appCallWithOneTx($algorand, $account, $applicationId);
        // THIS WILL THROWS THE FOLLOWING ERROR:
        // logic eval error: pc= 68 dynamic cost budget exceeded, executing ed25519verify: remaining budget is 700 but program cost was 1928.

        self::appCallWithThreeTxs($algorand, $account, $applicationId);
        // THIS WILL SUCCEED
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


    public static function appCallWithOneTx(Algorand $algorand, Account $account, int $applicationId)
    {
        $verify = str_repeat('a', 64);

        // Group transactions
        $transactions = [
            self::getApplicationCallTransaction($algorand, $account, $applicationId, "str:$verify,str:$verify")
        ];

        // Sign the transaction
        $signedTxs = [];
        foreach ($transactions as $tx) {
            $signedTxs[] = $tx->sign($account);
        }

        // Broadcast the transaction
        $pendingTx = $algorand->sendTransactions($signedTxs, true);
        prettyPrint('Result confirmed in round address: ' . $pendingTx->confirmedRound);
    }

    public static function appCallWithThreeTxs(Algorand $algorand, Account $account, int $applicationId)
    {
        $verify = str_repeat('r', 64);

        // Group transactions
        $transactions = AtomicTransfer::group([
            self::getApplicationCallTransaction($algorand, $account, $applicationId, "str:$verify,str:$verify"),
            self::getApplicationCallTransaction($algorand, $account, $applicationId),
            self::getApplicationCallTransaction($algorand, $account, $applicationId),
        ]);

        // Sign the transaction
        $signedTxs = [];
        foreach ($transactions as $tx) {
            $signedTxs[] = $tx->sign($account);
        }

        // Broadcast the transaction
        $pendingTx = $algorand->sendTransactions($signedTxs, true);
        prettyPrint('Result confirmed in round address: ' . $pendingTx->confirmedRound);
    }


    /**
     * @throws AlgorandException
     */
    public static function getApplicationCallTransaction(Algorand $algorand, Account $sender, int $applicationId, ?string $arguments = null): ApplicationBaseTransaction
    {

        if ($arguments != null) {
            $arguments = AlgorandUtils::parse_application_arguments($arguments);
        }
        return TransactionBuilder::applicationCall()
            ->sender($sender->getAddress())
            ->applicationId($applicationId)
            ->arguments($arguments)
            ->noteText(self::generateRandomString())
            ->useSuggestedParams($algorand)
            ->build();
    }

    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

OpPoolExample::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}