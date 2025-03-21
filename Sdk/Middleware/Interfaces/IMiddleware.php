<?php
declare(strict_types=1);

namespace Sdk\Middleware\Interfaces;

use Exception;
use Sdk\Http\Request;
use Sdk\Http\Response;

interface IMiddleware
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws Exception
     */
    public function execute(Request $request, Response $response, array $args): Response;
}