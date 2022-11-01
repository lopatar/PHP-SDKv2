<?php
declare(strict_types=1);

namespace App\Controllers;

use Sdk\Http\Request;
use Sdk\Http\Response;
use Sdk\Middleware\CSRF;

class Home
{
	public static function main(Request $request, Response $response, array $args): Response
	{
		CSRF::setTokenHeader($response);
		$response->writeLine('Check headers for token', true);
		return $response;
	}
}