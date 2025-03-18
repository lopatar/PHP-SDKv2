<?php

namespace Sdk\Middleware;

use Sdk\Http\Request;
use Sdk\Http\Response;

final readonly class Redirect implements Interfaces\IMiddleware
{
    public function __construct(public string $to)
    {
    }

    public function execute(Request $request, Response $response, array $args): Response
    {
        $response->redirect($this->to);
    }
}