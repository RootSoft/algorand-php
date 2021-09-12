<?php

namespace Rootsoft\Algorand;

use JsonMapper\JsonMapperFactory;
use JsonMapper\JsonMapperInterface;
use JsonMapper\Middleware\CaseConversion;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Clients\KmdClient;
use Rootsoft\Algorand\Indexer\AlgorandIndexer;
use Rootsoft\Algorand\KMD\Api\KmdApi;
use Rootsoft\Algorand\Managers\AccountManager;
use Rootsoft\Algorand\Managers\ApplicationManager;
use Rootsoft\Algorand\Managers\AssetManager;
use Rootsoft\Algorand\Services\ManagesBalanceV2;
use Rootsoft\Algorand\Services\ManagesBlocksV2;
use Rootsoft\Algorand\Services\ManagesCatchupsV2;
use Rootsoft\Algorand\Services\ManagesLedgerV2;
use Rootsoft\Algorand\Services\ManagesNodesV2;
use Rootsoft\Algorand\Services\ManagesParticipationV2;
use Rootsoft\Algorand\Services\ManagesTransactionParamsV2;
use Rootsoft\Algorand\Services\ManagesTransactionsV2;
use Rootsoft\Algorand\Traits\MakesHttpRequests;

/**
 *
 * Class Algorand
 * @package Rootsoft\Algorand
 */
class Algorand
{
    use MakesHttpRequests;
    use ManagesNodesV2;
    use ManagesTransactionsV2;
    use ManagesTransactionParamsV2;
    use ManagesBalanceV2;
    use ManagesBlocksV2;
    use ManagesCatchupsV2;
    use ManagesLedgerV2;
    use ManagesParticipationV2;

    /**
     * The Guzzle HTTP Client instance to interact with algod.
     */
    private AlgodClient $algodClient;

    /**
     * The Guzzle HTTP Client instance to interact with the indexer.
     */
    private IndexerClient $indexerClient;

    /**
     * The Guzzle HTTP Client instance to interact with the key management daemon.
     */
    private KmdClient $kmdClient;

    /**
     * Mapping responses to models.
     */
    private JsonMapperInterface $jsonMapper;

    /**
     * The AlgorandIndexer provides a set of REST API calls for searching blockchain Transactions, Accounts, Assets and Blocks.
     * Each of these calls also provides several filter parameters to support refining searches.
     *
     * @var AlgorandIndexer
     */
    private AlgorandIndexer $indexer;

    /**
     * Handles everything related to accounts.
     *
     * @var AccountManager
     */
    private AccountManager $accountManager;

    /**
     * Handles everything related to assets.
     *
     * @var AssetManager
     */
    private AssetManager $assetManager;

    /**
     * Handles everything related to applications.
     *
     * @var ApplicationManager
     */
    private ApplicationManager $applicationManager;

    /**
     * Algorand constructor.
     *
     * @param AlgodClient $algodClient
     * @param IndexerClient $indexerClient
     * @param KmdClient|null $kmdClient
     */
    public function __construct(AlgodClient $algodClient, IndexerClient $indexerClient, ?KmdClient $kmdClient = null)
    {
        $this->algodClient = $algodClient;
        $this->indexerClient = $indexerClient;
        $this->kmdClient = $kmdClient ?? new KmdClient('127.0.0.1');

        $this->jsonMapper = (new JsonMapperFactory())->bestFit();
        $this->jsonMapper->push(new CaseConversion(
            \JsonMapper\Enums\TextNotation::KEBAB_CASE(),
            \JsonMapper\Enums\TextNotation::CAMEL_CASE()
        ));

        $this->accountManager = new AccountManager($this->algodClient, $this->jsonMapper);
        $this->assetManager = new AssetManager($this->algodClient, $this->indexerClient, $this->jsonMapper);
        $this->applicationManager = new ApplicationManager($this->algodClient, $this->jsonMapper);
        $this->indexer = new AlgorandIndexer($this->indexerClient, $this->jsonMapper);
    }

    /**
     * Instantiate a new Algorand instance from the urls.
     *
     * @param string $algodUrl
     * @param string $indexerUrl
     * @param string $apiKey
     * @param string $algodTokenKey
     * @param string $indexerTokenKey
     * @return Algorand
     */
    public static function url(
        string $algodUrl,
        string $indexerUrl,
        string $apiKey,
        string $algodTokenKey = AlgodClient::ALGOD_API_TOKEN,
        string $indexerTokenKey = IndexerClient::INDEXER_API_TOKEN
    ) {
        // Create clients from url
        $algodClient = new AlgodClient($algodUrl, $apiKey, $algodTokenKey);
        $indexerClient = new IndexerClient($indexerUrl, $apiKey, $indexerTokenKey);
        $instance = new self($algodClient, $indexerClient);

        return $instance;
    }

    /**
     * The AlgorandIndexer provides a set of REST API calls for searching blockchain Transactions, Accounts, Assets and Blocks.
     * Each of these calls also provides several filter parameters to support refining searches.
     *
     * @return \Rootsoft\Algorand\Indexer\AlgorandIndexer
     */
    public function indexer(): AlgorandIndexer
    {
        return $this->indexer;
    }

    /**
     * The Key Management Daemon (kmd) is a low level wallet and key management tool.
     * It works in conjunction with algod and goal to keep secrets safe.
     * kmd tries to ensure that secret keys never touch the disk unencrypted.
     *
     * @return \Rootsoft\Algorand\KMD\Api\KmdApi
     */
    public function kmd(): KmdApi
    {
        return $this->kmdClient->getApi();
    }

    /**
     * @return \Rootsoft\Algorand\Managers\AccountManager
     */
    public function accountManager(): AccountManager
    {
        return $this->accountManager;
    }

    /**
     * Manager related to everything regarding ASA.
     *
     * @return \Rootsoft\Algorand\Managers\AssetManager
     */
    public function assetManager(): AssetManager
    {
        return $this->assetManager;
    }

    /**
     * Manager related to everything regarding Applications.
     *
     * @return \Rootsoft\Algorand\Managers\ApplicationManager
     */
    public function applicationManager(): ApplicationManager
    {
        return $this->applicationManager;
    }

    /**
     * Return the associated client used to perform queries.
     *
     * @return \Rootsoft\Algorand\Algorand
     */
    public function client()
    {
        return $this;
    }
}
