<?php

namespace Sdk\Utils\Encryption\Exceptions;

use Throwable;

class CryptoOperationFailed extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}