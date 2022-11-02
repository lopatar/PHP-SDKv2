<?php
declare(strict_types=1);

namespace Sdk\Routing\Exceptions;

use Sdk\Routing\Entities\Route;
use Throwable;

final class RouteAlreadyExists extends \Exception
{
	public function __construct(Route $newRoute, Route $existingRoute, int $code = 0, ?Throwable $previous = null)
	{
		$newRouteMethods = $this->buildRequestMethodString($newRoute);
		$existingMethods = $this->buildRequestMethodString($existingRoute);
		$message = "Tried to create route with the request path format ($newRoute->requestPathFormat). New route: $newRouteMethods, Existing route: $existingMethods";
		parent::__construct($message, $code, $previous);
	}

	private function buildRequestMethodString(Route $route): string
	{
		if (count($route->requestMethod) > 1) {
			$message = 'request methods: ';
			$methodCount = count($route->requestMethod) - 1; //-1 so we can iterate with <= and compare using === ()

			for ($i = 0; $i <= $methodCount; $i++) {
				$requestMethod = $route->requestMethod[$i]->value;
				$message .= ($i === $methodCount) ? $requestMethod : "$requestMethod, ";
			}
		} else {
			$message = 'request method: ' . $route->requestMethod[0]->value; //PHP cannot do double property access in "" string
		}

		return $message;
	}
}