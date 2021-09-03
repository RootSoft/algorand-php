<?php

namespace Rootsoft\Algorand\Templates\Parameters;

use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Utils\Buffer;

class AddressParameterValue extends ParameterValue
{
    public function __construct(int $offset, Address $address)
    {
        parent::__construct($offset, Buffer::toArray($address->address));
    }

    public function placeholderSize(): int
    {
        return 32;
    }
}
