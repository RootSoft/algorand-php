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
use Rootsoft\Algorand\Models\Transactions\Types\RawPaymentTransaction;
use Rootsoft\Algorand\Utils\AlgorandUtils;

class InnerTxnsExample
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
        $applicationId = 70280674; //self::createApp($algorand, $account);
        prettyPrint('Application id: ' . $applicationId);

        // Get the application address
        $applicationAddress = Address::forApplication($applicationId);
        prettyPrint('Application address: ' . $applicationAddress->encodedAddress);

        // Group the transaction
        $groupedTxs = AtomicTransfer::group(
            [
                self::getFundTransaction($algorand, $account, $applicationAddress, 500000),
                self::getApplicationCallTransaction($algorand, $account, $applicationId, 'str:inner-txn-demo,str:itxnd,int:1000'),
            ]
        );

        // Sign the transactions
        $signedTxs = [];
        foreach ($groupedTxs as $tx) {
            $signedTxs[] = $tx->sign($account);
        }

        // Broadcast the transaction
        $algorand->sendTransactions($signedTxs, true);

        // Get the account information
        $application = $algorand->accountManager()->getAccountInformation($applicationAddress->encodedAddress);
        print_r($application);
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

        // TODO EMPTY MAP AND ARRAY CHECK

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
     * @return RawPaymentTransaction
     * @throws AlgorandException
     */
    public static function getFundTransaction(Algorand $algorand, Account $sender, Address $receiver, int $amount): RawPaymentTransaction
    {
        return TransactionBuilder::payment()
            ->sender($sender->getAddress())
            ->receiver($receiver)
            ->amount($amount)
            ->useSuggestedParams($algorand)
            ->build();
    }

    /**
     * @throws AlgorandException
     */
    public static function getApplicationCallTransaction(Algorand $algorand, Account $sender, int $applicationId, string $arguments): ApplicationBaseTransaction
    {
        // Specify the arguments
        $arguments = AlgorandUtils::parse_application_arguments($arguments);

        return TransactionBuilder::applicationCall()
            ->sender($sender->getAddress())
            ->applicationId($applicationId)
            ->arguments($arguments)
            ->useSuggestedParams($algorand)
            ->build();
    }
}

InnerTxnsExample::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}