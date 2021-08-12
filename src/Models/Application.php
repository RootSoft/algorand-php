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

    /**
     * Round when this application was created.
     *
     * @var int|null
     */
    public ?int $createdAtRound = null;

    /**
     * Whether this application is currently deleted.
     *
     * @var bool|null
     */
    public ?bool $deleted = null;

    /**
     * Round when this application was deleted.
     *
     * @var int|null
     */
    public ?int $deletedAtRound = null;
}
