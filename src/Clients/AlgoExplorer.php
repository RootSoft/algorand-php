<?php

// Oct 7, 2022 
//
// USING UPDATED CORRECT URL ENDPOINTS FOR ALGOEXPLORER
//

namespace Rootsoft\Algorand\Clients;

class AlgoExplorer
{
    /**
     * The Algod API url for AlgoExplorer's MainNet.
     */
    public const MAINNET_ALGOD_API_URL = 'https://node.algoexplorerapi.io/';

    /**
     * The Algod API url for AlgoExplorer's TestNet.
     */
    public const TESTNET_ALGOD_API_URL = 'https://node.testnet.algoexplorerapi.io/';

    /**
     * The Algod API url for AlgoExplorer's BetaNet.
     */
    public const BETANET_ALGOD_API_URL = 'https://node.betanet.algoexplorerapi.io/';

    /**
     * The Indexer API url for AlgoExplorer's MainNet.
     */
    public const MAINNET_INDEXER_API_URL = 'https://algoindexer.algoexplorerapi.io/v2';

    /**
     * The Indexer API url for AlgoExplorer's TestNet.
     */
    public const TESTNET_INDEXER_API_URL = 'https://algoindexer.testnet.algoexplorerapi.io/';

    /**
     * The Indexer API url for AlgoExplorer's BetaNet.
     */
    public const BETANET_INDEXER_API_URL = 'https://algoindexer.betanet.algoexplorerapi.io/';
}
