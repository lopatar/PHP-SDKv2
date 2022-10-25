<?php
declare(strict_types=1);

namespace App\Controllers;

use Sdk\Http\Request;
use Sdk\Http\Response;

class Home
{
	public static function main(Request $request, Response $response, array $args): Response
	{
		$first = $args['first'];
		$last = $args['last'];

		$response->writeLine("$first $last", true);

		$testParameters = [
			'first' => 'test',
			'last' => 'last-test'
		];

		$routeUrl = $request->getRoute()?->generateUrl($testParameters);
		$response->writeLine("URL: $routeUrl", true);
		return $response;
	}
}