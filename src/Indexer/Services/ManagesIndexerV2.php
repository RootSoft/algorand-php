<?php

namespace Rootsoft\Algorand\Indexer\Services;

use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Applications\ApplicationLogsResult;
use Rootsoft\Algorand\Models\Indexer\IndexerHealth;

trait ManagesIndexerV2
{
    /**
     * Get the health status of the indexer.
     *
     * @return IndexerHealth
     * @throws AlgorandException
     */
    public function health()
    {
        $response = $this->get($this->indexerClient, '/health');

        $health = new IndexerHealth();
        $this->jsonMapper->mapObject($response, $health);

        return $health;
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
}
