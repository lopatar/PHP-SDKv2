<?php

namespace Sdk\Structures\Exceptions;

use Exception;
use Throwable;

final class StructureUnderflow extends Exception
{
    public function __construct(string $structureType, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct("$structureType underflow", $code, $previous);
    }
}