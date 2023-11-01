<?php

namespace Sdk\Structures\Exceptions;

use Throwable;

final class StackUnderflow extends \Exception
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct("Stack underflow", $code, $previous);
    }
}