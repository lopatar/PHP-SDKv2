<?php

namespace Sdk\Middleware\Entities;

use Sdk\IConfig;
use Sdk\Utils\Utils;

trait ConstructorLoggingTrait
{
    private static IConfig $configStatic;
    private readonly IConfig $config;

    public function __construct(IConfig $config)
    {
        $this->config = $config;
        self::$configStatic = $config;
        $this->checkLogPath();
    }

    private function checkLogPath(): void
    {
        $logPath = $this->config->getLoggingPath();

        if (!file_exists($logPath)) {
            Utils::printLine("[LOG] Log path does not exist: $logPath");
            die();
        }
    }
}