<?php
declare(strict_types=1);

namespace App\Controllers;

use Sdk\Http\Request;
use Sdk\Http\Response;

class Home
{
	public static function main(Request $request, Response $response, array $args): Response
	{
		return $response;
	}
}