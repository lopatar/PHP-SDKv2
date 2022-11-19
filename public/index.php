<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Sdk\App;

$app = new App();

$app->get('/{username}', 'Home::render');
$app->run();