<?php

namespace Rootsoft\Algorand\Indexer\Services;

use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Application;
use Rootsoft\Algorand\Models\Applications\ApplicationLogsResult;
use Rootsoft\Algorand\Models\Applications\SearchApplicationsResult;

trait ManagesIndexerApplicationsV2
{

    /**
     * Gets application information.
     *
     * Given a application id, it returns application information including creator, approval and clear programs,
     * global and local schemas, and global state.
     *
     * @param int $applicationId
     * @return \Rootsoft\Algorand\Models\Application
     * @throws AlgorandException
     */
    public function getApplicationById(int $applicationId)
    {
        $response = $this->get($this->indexerClient, "/v2/applications/$applicationId");

        $application = new Application();
        $this->jsonMapper->mapObject($response, $application);

        return $application;
    }

    /**
     * Lookup application logs by a given application id.
     *
     * @param int $applicationId
     * @return \Rootsoft\Algorand\Models\Applications\ApplicationLogsResult
     * @throws AlgorandException
     */
    public function getApplicationLogsById(int $applicationId): ApplicationLogsResult
    {
        $response = $this->get($this->indexerClient, "/v2/applications/$applicationId/logs");

        $result = new ApplicationLogsResult();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
    }

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
