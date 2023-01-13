<?php

namespace Sdk\Utils\Hashing\Exceptions;

use Throwable;

class InvalidPasswordAlgorithm extends \Exception
{
    public function __construct(string $algorithmName, int $code = 0, ?Throwable $previous = null)
    {
        $message = "$algorithmName is not an valid algorithm!";
        parent::__construct($message, $code, $previous);
    }
}