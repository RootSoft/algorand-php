<?php

namespace Rootsoft\Algorand\Clients;

/**
 * Algorand provides a standalone daemon algorand-indexer that reads committed blocks from the Algorand blockchain and
 * maintains a local database of transactions and accounts that are searchable and indexed.
 *
 * A REST API is available which enables application developers to perform rich and efficient queries on accounts,
 * transactions, assets, and so forth.
 *
 * Class IndexerClient
 */
class IndexerClient extends AlgorandClient
{
    /**
     * The default API Token Header for Indexer Algorand nodes.
     */
    public const INDEXER_API_TOKEN = 'X-Indexer-API-Token';

    /**
     * IndexerClient constructor.
     *
     * @param string $indexerUrl
     * @param string $apiKey
     * @param string $tokenKey
     * @param int $timeout
     */
    public function __construct(string $indexerUrl, ?string $apiKey = null, string $tokenKey = self::INDEXER_API_TOKEN, int $timeout = 0)
    {
        parent::__construct($indexerUrl, $apiKey, $tokenKey, $timeout);
    }
}
