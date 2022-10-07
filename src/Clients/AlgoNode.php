<?php

namespace Rootsoft\Algorand\Clients;

/**
 * AlgoNode REST client, see https://algonode.io/ for more details
 */
class AlgoNode
{
    /**
     * The Algod API url for AlgoExplorer's MainNet.
     */
    public const MAINNET_ALGOD_API_URL = 'https://mainnet-api.algonode.cloud';

    /**
     * The Algod API url for AlgoExplorer's TestNet.
     */
    public const TESTNET_ALGOD_API_URL = 'https://testnet-api.algonode.cloud';

    /**
     * The Algod API url for AlgoExplorer's BetaNet.
     */
    public const BETANET_ALGOD_API_URL = 'https://betanet-api.algonode.cloud';

    /**
     * The Indexer API url for AlgoExplorer's MainNet.
     */
    public const MAINNET_INDEXER_API_URL = 'https://mainnet-idx.algonode.cloud';

    /**
     * The Indexer API url for AlgoExplorer's TestNet.
     */
    public const TESTNET_INDEXER_API_URL = 'https://testnet-idx.algonode.cloud';

    /**
     * The Indexer API url for AlgoExplorer's BetaNet.
     */
    public const BETANET_INDEXER_API_URL = 'https://betanet-idx.algonode.cloud';
}
