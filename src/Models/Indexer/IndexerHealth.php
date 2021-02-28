<?php


namespace Rootsoft\Algorand\Models\Indexer;

/**
 * Class IndexerHealth
 * @package Rootsoft\Algorand\Models\Indexer
 */
class IndexerHealth
{

    /**
     * @var array|null
     */
    public ?array $data;

    public bool $dbAvailable;

    public bool $isMigrating;

    public string $message;

    public int $round;
}
