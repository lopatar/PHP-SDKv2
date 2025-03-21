<?php

namespace Sdk\Middleware\Interfaces;

use Exception;
use Sdk\Http\Request;
use Sdk\Http\Response;

interface ILoggingMiddleware
{
    public function log(Request $request, Response $response, array $args, Exception $e): void;
}