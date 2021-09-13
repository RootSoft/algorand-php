<?php

namespace Rootsoft\Algorand\Managers;

use GuzzleHttp\Client;
use JsonMapper\JsonMapperInterface;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Services\ManagesApplicationsV2;
use Rootsoft\Algorand\Traits\MakesHttpRequests;

/**
 * A Manager class used to easily perform application related tasks.
 *
 * Class AccountManager
 */
class ApplicationManager
{
    use MakesHttpRequests;
    use ManagesApplicationsV2;

    /**
     * @var Client
     */
    private AlgodClient $algodClient;

    /**
     * Automatically map json to PHP classes.
     * @var JsonMapperInterface
     */
    private JsonMapperInterface $jsonMapper;

    /**
     * AccountManager constructor.
     *
     * @param Client $client
     * @param JsonMapperInterface $jsonMapper
     */
    public function __construct(AlgodClient $algodClient, JsonMapperInterface $jsonMapper)
    {
        $this->algodClient = $algodClient;
        $this->jsonMapper = $jsonMapper;
    }
}
