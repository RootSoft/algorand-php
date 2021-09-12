<?php


namespace Rootsoft\Algorand\Clients;

use Rootsoft\Algorand\KMD\Api\KmdApi;
use Rootsoft\Algorand\KMD\Configuration;

/**
 * The Key Management Daemon (kmd) is a low level wallet and key management tool.
 * It works in conjunction with algod and goal to keep secrets safe.
 * kmd tries to ensure that secret keys never touch the disk unencrypted.
 *
 * See PSR-7 https://github.com/guzzle/psr7/pull/345
 */
class KmdClient extends AlgorandClient
{
    /**
     * The default API Token Header for Algorand nodes.
     */
    const KMD_API_TOKEN = 'X-KMD-API-Token';

    private KmdApi $api;

    /**
     * KmdClient constructor.
     *
     * @param string $apiUrl
     * @param string|null $apiKey
     * @param string $tokenKey
     * @param int $timeout
     */
    public function __construct(string $apiUrl, ?string $apiKey = null, string $tokenKey = self::KMD_API_TOKEN, int $timeout = 0)
    {
        parent::__construct($apiUrl, $apiKey, $tokenKey, $timeout);

        // Configure API key authorization: api_key
        $config = Configuration::getDefaultConfiguration()->setHost($apiUrl)->setApiKey($tokenKey, $apiKey);

        $this->api = new KmdApi(
            $this->client,
            $config
        );
    }

    /**
     * @return KmdApi
     */
    public function getApi(): KmdApi {
        return $this->api;
    }

}
