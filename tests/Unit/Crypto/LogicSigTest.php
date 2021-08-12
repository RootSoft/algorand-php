<?php


namespace Rootsoft\Algorand\Tests\Feature;

use DateTime;
use Orchestra\Testbench\TestCase;
use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Transactions\AtomicTransfer;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Utils\Algo;

class AlgorandTest extends TestCase
{

    public function testExample()
    {

    }

    private function asset()
    {
        // Create a new asset
        //$algorand->assetManager()->createNewAsset($account, 'Laracoin', 'LARA', 50000, 2);

        // Edit an existing asset
        //$algorand->assetManager()->editAsset(14192345, $account, $newAccount->getAddress());

        // Destroy an existing asset
        //$algorand->assetManager()->destroyAsset(14192345, $account);

        // Opt in to the asset
        //$algorand->assetManager()->optIn(14192345, $newAccount);

        //sleep(10);

        // Transfer the assets
        //$algorand->assetManager()->transfer(14192345, $account, 1000, $newAccount->getAddress());

        // Freeze an asset
        //$algorand->assetManager()->freeze(14192345, $account, $newAccount->getAddress(), false);

        // Revoke an asset
        // $algorand->assetManager()->revoke(14192345, $account, 1000, $newAccount->getAddress());
    }

    private function sendTransaction(Algorand $algorand, Account $account, Account $newAccount)
    {
        // Create a new transaction
        $transaction = TransactionBuilder::payment()
            ->sender($account->getAddress())
            ->note('Lets go! extra long text with some special things')
            ->amount(Algo::toMicroAlgos(1.2)) // 5 Algo
            ->receiver($newAccount->getAddress())
            ->useSuggestedParams($algorand)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction
        $txId = $algorand->sendTransaction($signedTransaction);
        dd($txId);
    }

    private function atomicTransfer(Algorand $algorand, Account $accountA, Account $accountB)
    {
        // Create a new transaction
        $transaction1 = TransactionBuilder::payment()
            ->sender($accountA->getAddress())
            ->note('Atomic transfer from account A to account B')
            ->amount(Algo::toMicroAlgos(1.2)) // 5 Algo
            ->receiver($accountB->getAddress())
            ->useSuggestedParams($algorand)
            ->build();

        // Create a new transaction
        $transaction2 = TransactionBuilder::payment()
            ->sender($accountB->getAddress())
            ->note('Atomic transfer from account B to account A')
            ->amount(Algo::toMicroAlgos(2)) // 5 Algo
            ->receiver($accountA->getAddress())
            ->useSuggestedParams($algorand)
            ->build();

        // Combine the transactions and calculate the group id
        $transactions = AtomicTransfer::group([$transaction1, $transaction2]);

        // Sign the transaction
        $signedTransaction1 = $transaction1->sign($accountA);
        $signedTransaction2 = $transaction2->sign($accountB);

        // Assemble transactions group
        $signedTransactions = [$signedTransaction1, $signedTransaction2];

        $txId = $algorand->sendTransactions($signedTransactions);
        dd($txId);
    }

    private function queryTransactions(Algorand $algorand, Account $account)
    {
        $afterDateTime = new DateTime();
        $afterDateTime = $afterDateTime->modify('-10 hours');

        // Query the indexer
        $transactions = $algorand->indexer()
            ->transactions()
            ->whereAddress($account->getAddress())
            ->search();

        dd($transactions);
    }
}
