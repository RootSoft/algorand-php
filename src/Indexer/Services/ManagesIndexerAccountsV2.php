<?php


namespace Rootsoft\Algorand\Indexer\Services;

use Rootsoft\Algorand\Models\Accounts\SearchAccountsResult;

trait ManagesIndexerAccountsV2
{
    /**
     * Search accounts using the indexer.
     *
     * @param array $queryParams
     * @return SearchAccountsResult List of accounts.
     */
    protected function searchAccounts(array $queryParams)
    {
        if (array_key_exists('balance-asset-id', $queryParams)) {
            return $this->searchAccountForBalances($queryParams['balance-asset-id'], $queryParams);
        }

        $response = $this->get($this->indexerClient, "/v2/accounts", $queryParams);

        return $this->jsonMapper->map($response, new SearchAccountsResult());
    }

    /**
     * Lookup the list of accounts who hold this asset.
     *
     * @param int $assetId
     * @param array $queryParams
     * @return SearchAccountsResult List of accounts.
     */
    protected function searchAccountForBalances(int $assetId, array $queryParams)
    {
        $response = $this->get($this->indexerClient, "/v2/assets/$assetId/balances", $queryParams);

        return $this->jsonMapper->map($response, new SearchAccountsResult());
    }
}
