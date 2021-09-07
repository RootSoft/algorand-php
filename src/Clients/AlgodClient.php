<?php


namespace Rootsoft\Algorand\Clients;

/**
 * An application connects to the Algorand blockchain through an algod client.
 * The algod client requires a valid algod REST endpoint IP address and algod token from an Algorand node that is
 * connected to the network you plan to interact with.
 *
 * Class AlgodClient
 * @package Rootsoft\Algorand\Clients
 */
class AlgodClient extends AlgorandClient
{
    /**
     * The default API Token Header for Algorand nodes.
     */
    const ALGOD_API_TOKEN = 'X-Algo-API-Token';

    /**
     * AlgodClient constructor.
     *
     * @param string $algodApiUrl
     * @param string $apiKey
     * @param string $tokenKey
     * @param int $timeout
     */
    public function __construct(string $algodApiUrl, ?string $apiKey = null, string $tokenKey = self::ALGOD_API_TOKEN, int $timeout = 0)
    {
        parent::__construct($algodApiUrl, $apiKey, $tokenKey, $timeout);
    }

}
