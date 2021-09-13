<?php

namespace Rootsoft\Algorand\Utils\Transformers;

interface MessagePackTransformer
{
    public function transform(string $className, array $data);

    public function type() : string;
}
