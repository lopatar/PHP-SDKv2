<?php
declare(strict_types=1);

use App\Config;
use Sdk\App;
use Sdk\Middleware\CSRF;
use Sdk\Middleware\Session;
use Sdk\Routing\Exceptions\RouteAlreadyExists;

require __DIR__ . '/../vendor/autoload.php';

$config = new Config();

$app = new App($config);

$session = new Session($config);

$app->get('/', 'Home::main')->addMiddleware($session);
$app->view('/csrf', 'Home.php')->addMiddlewareBulk([
	$session,
	new CSRF($config)
]);
$app->run();