<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Config;
use Sdk\App;

$app = new App(new Config());

$app->get('/{username}', 'Home::render');
$app->run();