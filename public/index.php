<?php
declare(strict_types=1);

use App\Config;
use Sdk\App;

require __DIR__ . '/../vendor/autoload.php';

$config = new Config();

$app = new App($config);
try {
	$app->any('/{test}/{lmao}', 'Home::main');
	$app->get('/{test}/{lmao}', 'Home::main');
} catch (\Sdk\Routing\Exceptions\RouteAlreadyExists $ex) {
	echo $ex->getMessage();
}
$app->run();