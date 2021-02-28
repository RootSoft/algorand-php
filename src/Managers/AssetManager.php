<?php


namespace Rootsoft\Algorand\Managers;

use GuzzleHttp\Client;
use JsonMapper;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Services\ManagesAssetsV2;
use Rootsoft\Algorand\Traits\MakesHttpRequests;

/**
 * The Algorand protocol supports the creation of on-chain assets that benefit from the same security, compatibility,
 * speed and ease of use as the Algo.
 * The official name for assets on Algorand is Algorand Standard Assets (ASA).
 *
 * With Algorand Standard Assets you can represent stablecoins, loyalty points, system credits, and in-game points,
 * just to name a few examples.
 * You can also represent single, unique assets like a deed for a house, collectable items,
 * unique parts on a supply chain, etc.
 *
 * There is also optional functionality to place transfer restrictions on an asset that help support
 * securities, compliance, and certification use cases.
 *
 * More information see: https://developer.algorand.org/docs/features/asa/
 * Class AccountManager
 * @package Rootsoft\Algorand\Managers
 */
class AssetManager
{
    use MakesHttpRequests;
    use ManagesAssetsV2;

    /**
     * Client used to perform algod operations.
     * @var AlgodClient
     */
    private AlgodClient $algodClient;

    /**
     * Client used to perform indexing operations.
     * @var IndexerClient
     */
    private IndexerClient $indexerClient;

    /**
     * Automatically map json to PHP classes.
     * @var JsonMapper
     */
    private JsonMapper $jsonMapper;

    /**
     * AssetManager constructor.
     * @param AlgodClient $algodClient
     * @param IndexerClient $indexerClient
     * @param JsonMapper $jsonMapper
     */
    public function __construct(AlgodClient $algodClient, IndexerClient $indexerClient, JsonMapper $jsonMapper)
    {
        $this->algodClient = $algodClient;
        $this->indexerClient = $indexerClient;
        $this->jsonMapper = $jsonMapper;
    }

}
