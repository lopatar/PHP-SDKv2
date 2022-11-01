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
		$existingRoute = $this->routeExists($route);

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

	/**
	 * Returns the existing {@see Route} object if it already exists within the {@see Router} object
	 * @param Route $routeToCheck
	 * @return Route|null
	 */
	public function routeExists(Route $routeToCheck): ?Route
	{
		foreach ($this->routes as $existingRoute) {
			if ($existingRoute->requestPathFormat === $routeToCheck->requestPathFormat) {
				//the == operator is used intentionally, we want equality not identity
				if ($existingRoute->requestMethod == $routeToCheck->requestMethod) {
					return $existingRoute;
				}
			}

			if ($existingRoute->name !== null && $existingRoute->name === $routeToCheck->name) {
				return $existingRoute;
			}
		}

		return null;
	}

	/**
	 * Gets route by its {@see Route::$name}
	 * @param string $name
	 * @return Route|null
	 */
	public function getRoute(string $name): ?Route
	{
		foreach ($this->routes as $route) {
			if ($route->name === $name) {
				return $route;
			}
		}

		return null;
	}
}