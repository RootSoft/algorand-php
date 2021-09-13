<?php

namespace Rootsoft\Algorand\Templates\Parameters;

abstract class ParameterValue
{
    private int $offset;

    private array $value;

    /**
     * @param int $offset
     * @param array $value
     */
    public function __construct(int $offset, array $value)
    {
        $this->offset = $offset;
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return array
     */
    public function toBytes(): array
    {
        return $this->value;
    }

    abstract public function placeholderSize(): int;
}
