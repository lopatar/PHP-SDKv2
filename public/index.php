<?php
declare(strict_types=1);

use App\Config;
use Sdk\App;
use Sdk\Routing\Exceptions\RouteAlreadyExists;

require __DIR__ . '/../vendor/autoload.php';

$config = new Config();

$app = new App($config);

$app->get('/{first}/{last}', 'Home::main');

$app->run();