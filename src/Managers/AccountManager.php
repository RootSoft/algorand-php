<?php


namespace Rootsoft\Algorand\Managers;

use GuzzleHttp\Client;
use JsonMapper;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Services\ManagesAccountsV2;
use Rootsoft\Algorand\Services\ManagesBalanceV2;
use Rootsoft\Algorand\Traits\MakesHttpRequests;

/**
 * Algorand uses Ed25519 high-speed, high-security elliptic-curve signatures.
 * The keys are produced through standard, open-source cryptographic libraries packaged with each of the SDKs.
 * The key generation algorithm takes a random value as input and outputs two 32-byte arrays, representing a
 * public key and its associated private key.
 *
 * These are also referred to as a public/private key pair.
 * These keys perform important cryptographic functions like signing data and verifying signatures.
 *
 * More information see: https://developer.algorand.org/docs/features/accounts/
 * Class AccountManager
 * @package Rootsoft\Algorand\Managers
 */
class AccountManager
{
    use MakesHttpRequests;
    use ManagesAccountsV2;
    use ManagesBalanceV2;

    /**
     *
     * @var Client
     */
    private AlgodClient $algodClient;

    /**
     * Automatically map json to PHP classes.
     * @var JsonMapper
     */
    private JsonMapper $jsonMapper;

    /**
     * AccountManager constructor.
     *
     * @param Client $client
     * @param JsonMapper $jsonMapper
     */
    public function __construct(AlgodClient $algodClient, JsonMapper $jsonMapper)
    {
        $this->algodClient = $algodClient;
        $this->jsonMapper = $jsonMapper;
    }
}
