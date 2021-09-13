<?php

namespace Rootsoft\Algorand\Indexer\Services;

use Rootsoft\Algorand\Models\Indexer\IndexerHealth;

trait ManagesIndexerV2
{
    /**
     * Get the health status of the indexer.
     *
     * @return IndexerHealth
     */
    public function health()
    {
        $response = $this->get($this->indexerClient, '/health');

        $health = new IndexerHealth();
        $this->jsonMapper->mapObject($response, $health);

        return $health;
    }
}
