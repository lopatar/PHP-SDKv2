<?php

namespace Sdk\Structures\Interfaces;
interface IStack
{
    public function __construct(int $size);
    public function push(mixed $value): void;
    public function pop(): mixed;
    public function isEmpty(): bool;
    public function isFull(): bool;
    public function reverse(): void;
}