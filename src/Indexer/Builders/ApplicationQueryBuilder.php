<?php

namespace Rootsoft\Algorand\Indexer\Builders;

use JsonMapper\JsonMapperInterface;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Indexer\QueryBuilder;
use Rootsoft\Algorand\Indexer\Services\ManagesIndexerApplicationsV2;
use Rootsoft\Algorand\Models\Applications\SearchApplicationsResult;
use Rootsoft\Algorand\Traits\MakesHttpRequests;

class ApplicationQueryBuilder extends QueryBuilder
{
    use MakesHttpRequests;
    use ManagesIndexerApplicationsV2;

    /**
     * AlgorandIndexer constructor.
     *
     * @param IndexerClient $client
     * @param JsonMapperInterface $jsonMapper
     */
    public function __construct(IndexerClient $client, JsonMapperInterface $jsonMapper)
    {
        parent::__construct($client, $jsonMapper);
    }

    /**
     * Include results with the given application id.
     *
     * @param int $applicationId
     * @return $this
     */
    public function whereApplicationId(int $applicationId)
    {
        $this->setParameter('application-id', $applicationId);

        return $this;
    }

    /**
     * Fetch the applications.
     *
     * @param int|null $limit
     * @return SearchApplicationsResult
     * @throws \Rootsoft\Algorand\Exceptions\AlgorandException
     */
    public function search(?int $limit = null)
    {
        if (! is_null($limit)) {
            $this->limit($limit);
        }

        return $this->searchApplications($this->payload);
    }
}
