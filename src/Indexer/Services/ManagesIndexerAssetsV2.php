<?php

namespace Rootsoft\Algorand\Indexer\Services;

use Rootsoft\Algorand\Models\Assets\SearchAssetsResult;

trait ManagesIndexerAssetsV2
{
    /**
     * Search assets using the indexer.
     *
     * @param array $queryParams
     * @return SearchAssetsResult List of assets.
     */
    protected function searchAssets(array $queryParams)
    {
        $response = $this->get($this->indexerClient, '/v2/assets', $queryParams);

        $result = new SearchAssetsResult();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
    }
}
