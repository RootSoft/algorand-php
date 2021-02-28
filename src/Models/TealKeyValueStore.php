<?php


namespace Rootsoft\Algorand\Models;

/**
 * Represents a key-value store for use in an application.
 * Class TealKeyValueStore
 * @package Rootsoft\Algorand\Models
 */
class TealKeyValueStore
{
    /**
     * The teal key
     * @var string
     * @required
     */
    public string $key;

    /**
     * The TEAL value for the given key
     * @var TealValue
     * @required
     */
    public TealValue $value;
}
