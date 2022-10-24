<?php
declare(strict_types=1);

namespace Sdk\Routing;

use Sdk\Http\Request;
use Sdk\Routing\Entities\Route;
use Sdk\Routing\Exceptions\RouteAlreadyExists;

/**
 * Object that is responsible for managing routes
 * @uses \Sdk\Routing\Entities\Route
 */
final class Router
{
	/**
	 * @var Route[]
	 */
	private array $routes = [];

	/**
	 * @throws RouteAlreadyExists
	 */
	public function addRoute(Route $route): self
	{
		$existingRoute = $this->routePathExists($route->requestPathFormat);

		if ($existingRoute !== null) {
			throw new RouteAlreadyExists($route, $existingRoute);
		}

		$this->routes[] = $route;
		return $this;
	}

	public function matchRoute(Request $request): ?Route
	{
		foreach ($this->routes as $route) {
			if ($route->match($request)) {
				return $route;
			}
		}

		return null;
	}

	public function routePathExists(string $routePathFormat): ?Route
	{
		foreach ($this->routes as $route) {
			if ($route->requestPathFormat === $routePathFormat) {
				return $route;
			}
		}

		return null;
	}
}