<?php


namespace Rootsoft\Algorand\Models;

class GenesisInformation
{
    /**
     * @var Allocation[]
     */
    public array $allocations;
    public string $fees;
    public string $id;
    public string $network;
    public string $proto;
    public string $rwd;
    public int $timestamp;

    /**
     * @param Allocation[] $allocations
     */
    public function setAlloc(array $allocations)
    {
        $this->allocations = $allocations;
    }
}
