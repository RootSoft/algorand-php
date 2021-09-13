<?php

namespace Rootsoft\Algorand\Clients;

class PureStake
{
    /**
     * The default API Token Header for PureStake nodes.
     */
    public const API_TOKEN_HEADER = 'x-api-key';

    /**
     * The Algod API url for PureStake's MainNet.
     */
    public const MAINNET_ALGOD_API_URL = 'https://mainnet-algorand.api.purestake.io/ps2';

    /**
     * The Algod API url for PureStake's TestNet.
     */
    public const TESTNET_ALGOD_API_URL = 'https://testnet-algorand.api.purestake.io/ps2';

    /**
     * The Algod API url for PureStake's BetaNet.
     */
    public const BETANET_ALGOD_API_URL = 'https://betanet-algorand.api.purestake.io/ps2';

    /**
     * The Indexer API url for PureStake's MainNet.
     */
    public const MAINNET_INDEXER_API_URL = 'https://mainnet-algorand.api.purestake.io/idx2';

    /**
     * The Indexer API url for PureStake's TestNet.
     */
    public const TESTNET_INDEXER_API_URL = 'https://testnet-algorand.api.purestake.io/idx2';

    /**
     * The Indexer API url for PureStake's BetaNet.
     */
    public const BETANET_INDEXER_API_URL = 'https://betanet-algorand.api.purestake.io/idx2';
}
