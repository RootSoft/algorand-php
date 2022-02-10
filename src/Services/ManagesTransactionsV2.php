<?php

namespace Rootsoft\Algorand\Services;

use JsonMapper\JsonMapperInterface;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\PendingTransactionsResult;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\PendingTransaction;
use Rootsoft\Algorand\Models\Transactions\SignedTransaction;
use Rootsoft\Algorand\Models\Transactions\TransactionResult;
use Rootsoft\Algorand\Utils\Encoder;

trait ManagesTransactionsV2
{
    private JsonMapperInterface $jsonMapper;

    /**
     * Send a payment to the given recipient with the recommended transaction parameters.
     *
     * @param \Rootsoft\Algorand\Models\Accounts\Account $account The account that is sending & authorizing the payment
     * @param \Rootsoft\Algorand\Models\Accounts\Address $recipient The recipient
     * @param int $microAlgos The amount of micro algos
     * @param string|null $note An optional note to add
     *
     * @return string The transaction id.
     * @throws \Rootsoft\Algorand\Exceptions\AlgorandException
     * @throws \SodiumException
     */
    public function sendPayment(Account $account, Address $recipient, int $microAlgos, ?string $note = null)
    {
        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Create a new transaction
        $transaction = TransactionBuilder::payment()
            ->sender($account->getAddress())
            ->note($note)
            ->amount($microAlgos)
            ->receiver($recipient)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction
        return $this->sendTransaction($signedTransaction);
    }

    /**
     * Lookup a single transaction by its id.
     *
     * @param string $transactionId
     * @return \Rootsoft\Algorand\Models\Transactions\TransactionResult
     */
    public function getTransactionById(string $transactionId)
    {
        $response = $this->get($this->indexerClient, "/v2/transactions/$transactionId");

        $result = new TransactionResult();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
    }

    /**
     * Broadcast a new (signed) transaction on the network.
     *
     * @param \Rootsoft\Algorand\Models\Transactions\SignedTransaction|string $transaction
     * @param bool $waitForConfirmation True if you want to wait for confirmation.
     * @param int $timeout How many rounds do you wish to check pending transactions for.
     *
     * @return string|\Rootsoft\Algorand\Models\Transactions\PendingTransaction The id of the transaction
     * or the pending transaction when using wait for confirmation.
     * @throws \Rootsoft\Algorand\Exceptions\AlgorandException
     */
    public function sendTransaction($transaction, bool $waitForConfirmation = false, int $timeout = 5)
    {
        $encodedTxBytes = $transaction;
        if ($transaction instanceof SignedTransaction) {
            $encodedTxBytes = Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        }

        $response = $this->post($this->algodClient, '/v2/transactions', [], ['body' => $encodedTxBytes], ['Content-Type' => 'application/x-binary']);

        if (!$waitForConfirmation) {
            return $response->txId;
        }

        return $this->waitForConfirmation($response->txId, $timeout);
    }

    /**
     * Broadcast a new (signed) transaction on the network.
     *
     * @param \Rootsoft\Algorand\Models\Transactions\SignedTransaction[] $transactions
     * @return PendingTransaction|string
     * @throws AlgorandException
     */
    public function sendTransactions(array $transactions, bool $waitForConfirmation = false, int $timeout = 5)
    {
        $encodedTxBytes = '';
        foreach ($transactions as $transaction) {
            $encodedTxBytes .= Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        }

        $response = $this->post($this->algodClient, '/v2/transactions', [], ['body' => $encodedTxBytes], ['Content-Type' => 'application/x-binary']);

        if (!$waitForConfirmation) {
            return $response->txId;
        }

        return $this->waitForConfirmation($response->txId, $timeout);
    }

    /**
     * Get a list of unconfirmed transactions, currently in the transaction pool by address.
     *
     * Get the list of pending transactions by address, sorted by priority, in decreasing order,
     * truncated at the end at MAX.
     *
     * If MAX = 0, returns all pending transactions.
     * @param string $address
     * @param int $max
     * @return \Rootsoft\Algorand\Models\PendingTransactionsResult
     */
    public function getPendingTransactions(string $address, $max = 10)
    {
        $response = $this->get($this->algodClient, "/v2/accounts/$address/transactions/pending", ['max' => $max]);

        $result = new PendingTransactionsResult();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
    }

    /**
     * Get a specific pending transaction.
     *
     * Given a transaction id of a recently submitted transaction, it returns information about it.
     * There are several cases when this might succeed:
     * - transaction committed (committed round > 0)
     * - transaction still in the pool (committed round = 0, pool error = "")
     * (committed round = 0, pool error != "")
     *
     * Or the transaction may have happened sufficiently long ago that the node
     * no longer remembers it, and this will return an error.
     *
     * @param string $transactionId
     * @return \Rootsoft\Algorand\Models\Transactions\PendingTransaction
     */
    public function getPendingTransactionById(string $transactionId)
    {
        $response = $this->get($this->algodClient, "/v2/transactions/pending/$transactionId");

        $result = new PendingTransaction();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
    }

    /**
     * Utility function to wait on a transaction to be confirmed.
     *
     * The timeout parameter indicates how many rounds do you wish to check pending transactions for.
     *
     * On Algorand, transactions are final as soon as they are incorporated into a block and blocks are produced,
     * on average, every 5 seconds.
     *
     * This means that transactions are confirmed, on average, in 5 seconds
     * @param string $transactionId
     * @param int $timeout
     * @return \Rootsoft\Algorand\Models\Transactions\PendingTransaction
     * @throws \Rootsoft\Algorand\Exceptions\AlgorandException
     */
    public function waitForConfirmation(string $transactionId, int $timeout = 5)
    {
        $status = $this->status();
        $startRound = $status->lastRound + 1;
        $currentRound = $startRound;

        while ($currentRound < ($startRound + $timeout)) {
            $pendingTx = $this->getPendingTransactionById($transactionId);
            $confirmedRound = $pendingTx->confirmedRound;
            if ($confirmedRound != null && $confirmedRound > 0) {
                return $pendingTx;
            }

            $this->statusAfterRound($currentRound);
            $currentRound++;
        }

        throw new AlgorandException("Transaction not confirmed after $timeout rounds.");
    }
}
