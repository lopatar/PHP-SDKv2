<?php

namespace Sdk\Middleware;

use Exception;
use Sdk\Http\Request;
use Sdk\Http\Response;
use Sdk\Middleware\Entities\ConstructorLoggingTrait;
use Sdk\Middleware\Entities\LoggingObject;
use Sdk\Middleware\Interfaces\ILoggingMiddleware;

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
        $handle = fopen($this->config->getLoggingPath(), "a");
        fwrite($handle, $log->request->url->path . ":" . $log->exception->getMessage() . "\n");
        fclose($handle);
    }

    private function logDev(LoggingObject $log): void
    {
        $handle = fopen($this->config->getLoggingPath(), "a");
        fwrite($handle, print_r($log, true));
        fclose($handle);
    }
}