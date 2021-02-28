<?php


namespace Rootsoft\Algorand\Clients;

use GuzzleHttp\Client;
use Rootsoft\Algorand\Utils\AlgorandUtils;

abstract class AlgorandClient
{

    /**
     * The API rest endpoint url for Algod.
     * @var string
     */
    private string $apiUrl;

    /**
     * The API Key
     * @var string
     */
    private string $apiKey;

    /**
     * The token header key to be used.
     * @var string
     */
    private string $tokenKey;

    /**
     * Number of seconds describing the total timeout of the request in seconds.
     * Use 0 to wait indefinitely (the default behavior).
     *
     * @var int
     */
    private int $timeout;

    /**
     * The current version of the API to use.
     */
    private string $version = 'v2';

    /**
     * The Guzzle HTTP Client instance.
     */
    public Client $client;

    /**
     * AlgorandClient constructor.
     *
     * @param string $apiUrl
     * @param string $apiKey
     * @param string $tokenKey
     * @param int $timeout
     */
    public function __construct(string $apiUrl, string $apiKey, string $tokenKey, int $timeout = 0)
    {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
        $this->tokenKey = $tokenKey;
        $this->timeout = $timeout;

        $this->setApiKey($apiKey);
    }

    /**
     * Set the api key and setup the Guzzle client.
     *
     * @param string $apiKey
     * @return $this
     */
    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;

        $this->client = new Client([
            'base_uri' => AlgorandUtils::format_url($this->apiUrl),
            'timeout' => $this->timeout,
            'http_errors' => false,
            'headers' => [
                $this->tokenKey => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        return $this;
    }

    /**
     * Set a new timeout.
     *
     * @param int $timeout
     * @return $this
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Get the timeout.
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Sets the version to use.
     *
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version.
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}
