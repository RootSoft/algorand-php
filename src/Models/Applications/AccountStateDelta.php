<?php

namespace Rootsoft\Algorand\Models\Applications;

class AccountStateDelta
{

    public ?string $address = null;

    /**
     * @var array|EvalDeltaKeyValue[]
     */
    public array $delta = [];
}