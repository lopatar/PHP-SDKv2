<?php

namespace Sdk\Structures;

use Sdk\Structures\Exceptions\StackOverflow;
use Sdk\Structures\Exceptions\StackUnderflow;

final class Stack implements Interfaces\IStack
{
    private array $values;
    private int $topIndex = -1;

    public function __construct(public readonly int $size)
    {
    }

    /**
     * @throws StackOverflow
     */
    public function push(mixed $value): void
    {
        if ($this->isFull()) {
            throw new StackOverflow();
        }

        $this->topIndex++;
        $this->values[$this->topIndex] = $value;
    }

    /**
     * @throws StackUnderflow
     */
    public function pop(): mixed
    {
        if ($this->isEmpty()) {
            throw new StackUnderflow();
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

    public function isFull(): bool
    {
        return $this->topIndex === $this->size - 1;
    }
}