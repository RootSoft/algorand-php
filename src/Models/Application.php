<?php


namespace Rootsoft\Algorand\Models;

/**
 * Application index and its parameters
 * Class Application
 * @package Rootsoft\Algorand\Models
 */
class Application
{
    /**
     * The application index.
     * @var int
     * @required
     */
    public int $id;

    /**
     * The application parameters
     * @var ApplicationParams
     * @required
     */
    public ApplicationParams $params;
}
