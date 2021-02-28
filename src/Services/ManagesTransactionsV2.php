<?php


namespace Rootsoft\Algorand\Services;

use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\PendingTransactionsResult;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\SignedTransaction;
use Rootsoft\Algorand\Models\Transactions\TransactionResult;
use Rootsoft\Algorand\Utils\Encoder;

trait ManagesTransactionsV2
{
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

        return $this->jsonMapper->map($response, new TransactionResult());
    }

    /**
     * Broadcast a new (signed) transaction on the network.
     *
     * @param \Rootsoft\Algorand\Models\Transactions\SignedTransaction $transaction
     * @return string The id of the transaction.
     */
    public function sendTransaction(SignedTransaction $transaction)
    {
        $encodedTxBytes = Encoder::getInstance()->encodeMessagePack($transaction->toArray());
        $response = $this->post($this->algodClient, "/v2/transactions", [], ['body' => $encodedTxBytes], ['Content-Type' => 'application/x-binary']);

        return $response->txId;
    }

    /**
     * Broadcast a new (signed) transaction on the network.
     *
     * @param \Rootsoft\Algorand\Models\Transactions\SignedTransaction[] $transactions
     * @return string The id of the transaction.
     */
    public function sendTransactions(array $transactions)
    {
        $encodedTxBytes = '';
        foreach ($transactions as $transaction) {
            $encodedTxBytes .= Encoder::getInstance()->encodeMessagePack($transaction->toArray());
        }

        $response = $this->post($this->algodClient, "/v2/transactions", [], ['body' => $encodedTxBytes], ['Content-Type' => 'application/x-binary']);

        return $response->txId;
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
        $response = $this->get($this->algodClient, "/v2/accounts/$address/transactions/pending");

        return $this->jsonMapper->map($response, new PendingTransactionsResult(), ['max' => $max]);
    }
}
