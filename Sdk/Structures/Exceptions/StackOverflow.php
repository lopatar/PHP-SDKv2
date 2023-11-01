<?php

namespace Sdk\Structures\Exceptions;

use Throwable;

final class StackOverflow extends \Exception
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct("Stack overflow", $code, $previous);
    }
}