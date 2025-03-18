<?php

namespace Sdk\Structures\Interfaces;

interface IQueue
{
    public function __construct(int $size);

    public function push(mixed $value): void;

    public function pop(): mixed;

    public function getFirst(): mixed;

    public function getLast(): mixed;

    public function isEmpty(): bool;

    public function isFull(): bool;

    public function reset(): void;
}