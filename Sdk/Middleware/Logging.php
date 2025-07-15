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

    public function logException(Request $request, Response $response, array $args, Exception $e): never
    {
        $loggingObject = new LoggingObject($request, $response, $args, $e, $this->config->getLoggingPath());

        ($this->config->isProduction()) ? $this->logErrProd($loggingObject) : $this->logErrDev($loggingObject);
        die();
    }

    public static function logMessage(string $message, string $title = ""): void
    {
        $date = date('d/m/Y H:i:s');
        $handle = fopen(self::$configStatic->getLoggingPath(), "a");
        $message = ($title === "") ? "[Log] [$date] $message\n" : "[Log] [$title] [$date] $message\n";
        fwrite($handle, $message);
        fclose($handle);
    }

    private function logErrProd(LoggingObject $log): void
    {
        $handle = fopen($this->config->getLoggingPath(), "a");
        fwrite($handle, $log->request->url->path . ":" . $log->exception->getMessage() . "\n");
        fclose($handle);
    }

    private function logErrDev(LoggingObject $log): void
    {
        $handle = fopen($this->config->getLoggingPath(), "a");
        fwrite($handle, print_r($log, true));
        fclose($handle);
    }
}