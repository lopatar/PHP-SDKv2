<?php
declare(strict_types=1);

namespace Sdk\Middleware\Interfaces;

use Sdk\Http\Request;
use Sdk\Http\Response;

interface IMiddleware
{
	public function execute(Request $request, Response $response, array $args): Response;
}