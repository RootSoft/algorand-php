<?php

namespace Rootsoft\Algorand\Indexer\Services;

use Rootsoft\Algorand\Models\Applications\SearchApplicationsResult;

trait ManagesIndexerApplicationsV2
{
    /**
     * Search applications using the indexer.
     *
     * @param array $queryParams
     * @return SearchApplicationsResult List of applications.
     * @throws \Rootsoft\Algorand\Exceptions\AlgorandException
     */
    protected function searchApplications(array $queryParams)
    {
        $response = $this->get($this->indexerClient, '/v2/applications', $queryParams);

        $result = new SearchApplicationsResult();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
    }
}
