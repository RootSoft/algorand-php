<?php


namespace Rootsoft\Algorand\Indexer;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use JsonMapper;
use Rootsoft\Algorand\Clients\IndexerClient;

abstract class QueryBuilder
{

    /** @var array */
    protected array $payload = [];

    protected IndexerClient $indexerClient;
    protected JsonMapper $jsonMapper;

    /**
     * QueryBuilder constructor.
     * @param IndexerClient $client
     * @param Client|JsonMapper $jsonMapper
     */
    public function __construct(IndexerClient $client, $jsonMapper)
    {
        $this->indexerClient = $client;
        $this->jsonMapper = $jsonMapper;
    }

    /**
     * The next page of results. Use the next token provided by the previous results.
     *
     * @param string $next
     * @return $this
     */
    public function next(string $next)
    {
        $this->setParameter('next', $next);

        return $this;
    }

    /**
     * Maximum number of results to return.
     *
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit)
    {
        $this->setParameter('limit', $limit);

        return $this;
    }

    /**
     * Set parameters.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setParameter(string $key, $value)
    {
        Arr::set($this->payload, $key, $value);

        return $this;
    }

    /**
     * Get parameters.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParameter(string $key, $default = null)
    {
        return Arr::get($this->payload, $key, $default);
    }
}
