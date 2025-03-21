<?php

namespace Sdk\Middleware;

use Exception;
use Sdk\Database\MariaDB\Connection;
use Sdk\Http\Request;
use Sdk\Http\Response;
use Sdk\Middleware\Entities\ConstructorLoggingTrait;
use Sdk\Middleware\Entities\LoggingObject;
use Sdk\Middleware\Interfaces\ILoggingMiddleware;
use Sdk\Utils\Utils;

/**
 * Logging middleware that handles the Exceptions thrown!
 * @internal
 */
class Logging implements ILoggingMiddleware
{
    use ConstructorLoggingTrait;

    public function log(Request $request, Response $response, array $args, Exception $e): never
    {
        $loggingObject = new LoggingObject($request, $response, $args, $e, $this->config->getLoggingPath());

        ($this->config->isProduction()) ? $this->logProd($loggingObject) : $this->logDev($loggingObject);
        die();
    }

    private function logProd(LoggingObject $log): void
    {

    }

    private function logDev(LoggingObject $log): void
    {

    }

    private function checkConfigPath(LoggingObject $log): void
    {
        $logPath = $this->config->getLoggingPath();

        if (!file_exists($logPath)) {
            Utils::printLine("[LOG] Log path does not exist: $logPath");
        }
    }
}