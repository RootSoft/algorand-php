<?php


namespace Rootsoft\Algorand\Indexer\Services;

use Rootsoft\Algorand\Models\Transactions\SearchTransactionsResult;

trait ManagesIndexerTransactionsV2
{
    /**
     * Search transactions using the indexer.
     *
     * @param array $queryParams
     * @return SearchTransactionsResult List of transactions.
     */
    protected function searchTransactions(array $queryParams)
    {
        if (array_key_exists('for_asset_id', $queryParams)) {
            return $this->searchTransactionsForAsset($queryParams['for_asset_id'], $queryParams);
        } elseif (array_key_exists('for_account_id', $queryParams)) {
            return $this->searchTransactionsForAccount($queryParams['for_account_id'], $queryParams);
        }

        $response = $this->get($this->indexerClient, "/v2/transactions", $queryParams);

        return $this->jsonMapper->map($response, new SearchTransactionsResult());
    }

    /**
     * Lookup transactions for an asset using the indexer.
     *
     * @param int $assetId
     * @param array $queryParams
     * @return SearchTransactionsResult List of transactions.
     */
    protected function searchTransactionsForAsset(int $assetId, array $queryParams)
    {
        $response = $this->get($this->indexerClient, "/v2/assets/$assetId/transactions", $queryParams);

        return $this->jsonMapper->map($response, new SearchTransactionsResult());
    }

    /**
     * Lookup transactions for an account using the indexer.
     *
     * @param string $accountId
     * @param array $queryParams
     * @return SearchTransactionsResult List of transactions.
     */
    protected function searchTransactionsForAccount(string $accountId, array $queryParams)
    {
        $response = $this->get($this->indexerClient, "/v2/accounts/$accountId/transactions", $queryParams);

        return $this->jsonMapper->map($response, new SearchTransactionsResult());
    }
}
