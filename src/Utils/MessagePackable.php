<?php

namespace Rootsoft\Algorand\Utils;

interface MessagePackable
{
    public function toMessagePack(): array;
}
