<?php
declare(strict_types=1);

use Sdk\App;
use App\Config;
use Sdk\Http\Request;
use Sdk\Http\Response;

$config = new Config();
$app = new App($config);

$app->get('/', function (Request $request, Response $response, array $args): Response {
	$response->write('Welcome to PHP-SDKv2');
	return $response;
});

$app->run();