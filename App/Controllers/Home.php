<?php
declare(strict_types=1);

namespace App\Controllers;

use Sdk\Http\Response;
use Sdk\Http\Request;
use Sdk\Middleware\CSRF;
use Sdk\Middleware\Session;
use Sdk\Utils\Random;

class Home
{
	public static function main(Request $request, Response $response, array $args): Response
	{
		return $response;
	}
}