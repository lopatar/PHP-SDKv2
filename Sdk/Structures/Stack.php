<?php

namespace Sdk\Structures;

use Sdk\Structures\Exceptions\StructureOverflow;
use Sdk\Structures\Exceptions\StructureUnderflow;

final class Stack implements Interfaces\IStack
{
    private array $values = [];
    private int $topIndex = -1;

    public function __construct(public readonly int $size)
    {
    }

    /**
     * @throws StructureUnderflow
     */
    public function push(mixed $value): void
    {
        if ($this->isFull()) {
            throw new StructureUnderflow('Stack');
        }

        $this->topIndex++;
        $this->values[$this->topIndex] = $value;
    }

    public function isFull(): bool
    {
        return $this->topIndex === $this->size - 1;
    }

    /**
     * @throws StructureOverflow
     */
    public function pop(): mixed
    {
        if ($this->isEmpty()) {
            throw new StructureOverflow('Stack');
        }

        $value = $this->values[$this->topIndex];
        unset($this->values[$this->topIndex]);
        $this->topIndex--;
        return $value;
    }

    public function isEmpty(): bool
    {
        return $this->topIndex < 0;
    }

    public function reverse(): void
    {
        $this->values = array_reverse($this->values);
    }
}