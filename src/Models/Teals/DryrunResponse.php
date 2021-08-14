<?php

namespace Rootsoft\Algorand\Models\Teals;

class DryrunResponse
{
    public string $error = '';

    public string $protocolVersion = '';

    public array $txns = [];
}
