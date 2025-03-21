<?php

namespace Sdk\Middleware;

use Sdk\Http\Request;
use Sdk\Http\Response;
use Sdk\Middleware\Entities\ConstructorConfigTrait;
use Sdk\Middleware\Interfaces\IMiddleware;

class Logging implements IMiddleware
{
    use ConstructorConfigTrait;

    public function execute(Request $request, Response $response, array $args): Response
    {

        return $response;
    }
}