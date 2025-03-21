<?php

namespace Sdk\Middleware\Entities;

use Exception;
use Sdk\Http\Entities\RequestMethod;
use Sdk\Http\Entities\StatusCode;
use Sdk\Http\Request;
use Sdk\Http\Response;

/**
 * @internal
 */
final readonly class LoggingObject
{
    public function __construct(public Request $request, public Response $response, public array $args, public Exception $exception, public string $path) {
    }
}