<?php

namespace Rootsoft\Algorand\Indexer\Services;

use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\SearchAccountsResult;
use Rootsoft\Algorand\Models\Assets\SearchAssetsResult;

trait ManagesIndexerAccountsV2
{

    /**
     * Get the created assets for the given address.
     *
     * @param string $address
     * @param array $params
     * @return \Rootsoft\Algorand\Models\Assets\SearchAssetsResult
     * @throws AlgorandException
     */
    public function getCreatedAssets(string $address, array $params = []): SearchAssetsResult
    {
        $response = $this->get($this->indexerClient, "/v2/accounts/$address/created-assets", $params);

        $result = new SearchAssetsResult();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
    }

    /**
     * Search accounts using the indexer.
     *
     * @param array $queryParams
     * @return SearchAccountsResult List of accounts.
     */
    protected function searchAccounts(array $queryParams)
    {
        if (array_key_exists('balance-asset-id', $queryParams)) {
            $balanceAssetId = $queryParams['balance-asset-id'];
            //unset($queryParams['balance-asset-id']);
            return $this->searchAccountForBalances($balanceAssetId, $queryParams);
        }

        $response = $this->get($this->indexerClient, '/v2/accounts', $queryParams);

        $result = new SearchAccountsResult();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
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

        $result = new SearchAccountsResult();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
    }
}
