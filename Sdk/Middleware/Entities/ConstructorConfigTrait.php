<?php

namespace Sdk\Middleware\Entities;

use Sdk\IConfig;

trait ConstructorConfigTrait
{
    private static IConfig $configStatic;
    private readonly IConfig $config;

    public function __construct(IConfig $config)
    {
        $this->config = $config;
        self::$configStatic = $config;
    }
}