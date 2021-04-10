<?php


namespace Rootsoft\Algorand\Indexer;

use JsonMapper\JsonMapperInterface;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Indexer\Builders\AccountQueryBuilder;
use Rootsoft\Algorand\Indexer\Builders\AssetQueryBuilder;
use Rootsoft\Algorand\Indexer\Builders\TransactionQueryBuilder;
use Rootsoft\Algorand\Indexer\Services\ManagesIndexerV2;
use Rootsoft\Algorand\Traits\MakesHttpRequests;

class AlgorandIndexer
{
    use MakesHttpRequests;
    use ManagesIndexerV2;

    /**
     * Client used to perform indexing operations.
     * @var IndexerClient
     */
    private IndexerClient $indexerClient;

    /**
     * Automatically map json to PHP classes.
     * @var JsonMapperInterface
     */
    private JsonMapperInterface $jsonMapper;

    /**
     * AlgorandIndexer constructor.
     * @param IndexerClient $indexerClient
     * @param JsonMapperInterface $jsonMapper
     */
    public function __construct(IndexerClient $indexerClient, JsonMapperInterface $jsonMapper)
    {
        $this->indexerClient = $indexerClient;
        $this->jsonMapper = $jsonMapper;
    }

    /**
     * Allow searching all transactions that have occurred on the blockchain.
     * This call contains many parameters to refine the search for specific values.
     *
     * @return TransactionQueryBuilder
     */
    public function transactions() : TransactionQueryBuilder
    {
        return new TransactionQueryBuilder($this->indexerClient, $this->jsonMapper);
    }

    /**
     * Allow searching all assets that have occurred on the blockchain.
     * This call contains many parameters to refine the search for specific values.
     *
     * @return AssetQueryBuilder
     */
    public function assets() : AssetQueryBuilder
    {
        return new AssetQueryBuilder($this->indexerClient, $this->jsonMapper);
    }

    /**
     * Allow searching all accounts on the blockchain.
     * This call contains many parameters to refine the search for specific values.
     *
     * @return AccountQueryBuilder
     */
    public function accounts() : AccountQueryBuilder
    {
        return new AccountQueryBuilder($this->indexerClient, $this->jsonMapper);
    }
}
