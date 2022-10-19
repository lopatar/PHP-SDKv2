<?php
declare(strict_types=1);

namespace Sdk\Routing;

use Sdk\Http\Request;
use Sdk\Routing\Entities\Route;

/**
 * Object that is responsible for managing routes
 * @uses \Sdk\Routing\Entities\Route
 */
final class Router
{
	/**
	 * @var Route[]
	 */
	private array $routes;

	public function addRoute(Route $route): self
	{
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
}