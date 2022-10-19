<?php
declare(strict_types=1);

use App\Config;
use Sdk\App;

require __DIR__ . '/../vendor/autoload.php';

$config = new Config();
$app = new App($config);

$app->run();