<?php

namespace Sdk\Middleware\Entities;

use Exception;
use Sdk\Http\Request;
use Sdk\Http\Response;

/**
 * @internal
 */
final class LoggingObject
{
    public function __construct(public readonly Request $request, public readonly Response $response, public readonly array $args, public Exception $exception, public string $path)
    {
    }
}