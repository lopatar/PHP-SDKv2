<?php

namespace Sdk\Middleware\Interfaces;

use Exception;
use Sdk\Http\Request;
use Sdk\Http\Response;

interface ILoggingMiddleware
{
    public function logException(Request $request, Response $response, array $args, Exception $e): never;
    public static function logMessage(string $message): void;
}