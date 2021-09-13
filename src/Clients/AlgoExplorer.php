<?php

namespace Rootsoft\Algorand\Clients;

class AlgoExplorer
{
    /**
     * The Algod API url for AlgoExplorer's MainNet.
     */
    public const MAINNET_ALGOD_API_URL = 'https://algoexplorerapi.io';

    /**
     * The Algod API url for AlgoExplorer's TestNet.
     */
    public const TESTNET_ALGOD_API_URL = 'https://testnet.algoexplorerapi.io';

    /**
     * The Algod API url for AlgoExplorer's BetaNet.
     */
    public const BETANET_ALGOD_API_URL = 'https://betanet.algoexplorerapi.io';

    /**
     * The Indexer API url for AlgoExplorer's MainNet.
     */
    public const MAINNET_INDEXER_API_URL = 'https://algoexplorerapi.io/idx2';

    /**
     * The Indexer API url for AlgoExplorer's TestNet.
     */
    public const TESTNET_INDEXER_API_URL = 'https://testnet.algoexplorerapi.io/idx2';

    /**
     * The Indexer API url for AlgoExplorer's BetaNet.
     */
    public const BETANET_INDEXER_API_URL = 'https://betanet.algoexplorerapi.io/idx2';
}
