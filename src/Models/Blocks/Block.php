<?php

namespace Rootsoft\Algorand\Models\Blocks;

class Block
{
    /**
     * @var string
     */
    public string $fees;

    public int $frac;

    public string $gen;

    public string $gh;

    public int $nextbefore;

    public string $nextproto;

    public int $nextswitch;

    public int $nextyes;

    public string $prev;

    public string $proto;

    public int $rate;

    public int $rnd;

    public int $rwcalr;

    public string $rwd;

    public string $seed;

    public int $ts;

    public string $txn;

    public array $txns;

    public string $upgradeprop;

    public bool $upgradeyes;
}
