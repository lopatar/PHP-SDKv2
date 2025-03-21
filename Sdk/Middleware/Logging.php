<?php

namespace Sdk\Middleware;

use Exception;
use Sdk\Http\Request;
use Sdk\Http\Response;
use Sdk\Middleware\Entities\ConstructorConfigTrait;
use Sdk\Middleware\Interfaces\ILoggingMiddleware;
use Sdk\Middleware\Interfaces\IMiddleware;

/**
 * Logging middleware that handles the Exceptions thrown!
 * @internal
 */
class Logging implements ILoggingMiddleware
{
    use ConstructorConfigTrait;

    public function log(Request $request, Response $response, array $args, Exception $e): never
    {

    }
}