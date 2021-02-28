<?php


namespace Rootsoft\Algorand\Models;

class ApplicationLocalState
{
    public int $id;
    public ApplicationStateSchema $schema;

    /**
     * @var TealKeyValueStore[]
     */
    public $keyValue;
}
