<?php

namespace Rootsoft\Algorand\Templates\Parameters;

use Rootsoft\Algorand\Crypto\Logic;

class IntParameterValue extends ParameterValue
{
    public function __construct(int $offset, int $value)
    {
        parent::__construct($offset, Logic::putUVarint($value));
    }

    public function placeholderSize(): int
    {
        return 1;
    }
}
