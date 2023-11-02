<?php

namespace Sdk\Structures;

use Sdk\Structures\Exceptions\StructureOverflow;
use Sdk\Structures\Exceptions\StructureUnderflow;

final class Queue implements Interfaces\IQueue
{
    private int $firstIndex = 0;
    private int $lastIndex = 0;

    private array $values = [];

    public function __construct(public readonly int $size)
    {
    }

    /**
     * @throws StructureOverflow
     */
    public function push(mixed $value): void
    {
        if ($this->isFull()) {
            throw new StructureOverflow('Queue');
        }

        $this->values[$this->lastIndex] = $value;
        $this->lastIndex++;
    }

    public function isFull(): bool
    {
        return $this->lastIndex === $this->size - 1;
    }

    /**
     * @throws StructureUnderflow
     */
    public function pop(): mixed
    {
        if ($this->isEmpty()) {
            throw new StructureUnderflow('Queue');
        }

        $value = $this->values[$this->firstIndex];
        $this->firstIndex++;
        return $value;
    }

    public function isEmpty(): bool
    {
        return $this->lastIndex === 0;
    }

    public function reset(): void
    {
        $this->firstIndex = 0;
        $this->lastIndex = 0;
        $this->values = [];
    }

    public function getFirst(): mixed
    {
        return ($this->firstIndex === 0) ? $this->values[$this->firstIndex] : $this->values[$this->firstIndex - 1];
    }

    public function getLast(): mixed
    {
        return ($this->lastIndex === 0) ? $this->values[$this->lastIndex] : $this->values[$this->lastIndex - 1];
    }
}