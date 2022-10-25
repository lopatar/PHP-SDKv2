<?php
declare(strict_types=1);

namespace Sdk\Routing;

use Sdk\Http\Request;
use Sdk\Routing\Entities\Route;
use Sdk\Routing\Entities\RouteParameterCollection;

/**
 * Object that allows us to easily match incoming {@link Request} and {@link Route} objects
 * @uses \Sdk\Routing\Entities\Route
 * @uses \Sdk\Http\Request
 */
final class RouteMatcher
{
	public function __construct(private readonly Route $route, private readonly Request $request) {}

	/**
	 * Whether the request method matches that route defined methods (can be multiple)
	 * @return bool False on failure
	 */
	public function matchRequestMethod(): bool
	{
		return in_array($this->request->method, $this->route->requestMethod);
	}

	/**
	 * Method that matches purely based on the request path, ignored parameters
	 * @return bool False on failure
	 */
	public function matchPlain(): bool
	{
		return $this->route->requestPathFormat === $this->request->getUrl()->path;
	}

	/**
	 * Returns whether the {@link Route::$parameters} matches {@link Url::$path}
	 *
	 * The method modified tha {@link Route::$parameters} object values
	 * @param string[] $routePathParts Route request path format split by '/';
	 * @param RouteParameterCollection $routeParameters Route parameters array
	 * @return bool False on failure
	 */
	public function matchParameters(array $routePathParts, RouteParameterCollection $routeParameters): bool
	{
		$requestPathParts = explode('/', $this->request->getUrl()->path); //We split the same as in Request::setUpParameters()

		if (!$this->matchPathPartCount($routePathParts, $requestPathParts)) {
			return false;
		}

		if (!$this->matchNonParamParts($routePathParts, $routeParameters, $requestPathParts)) {
			return false;
		}

		if (!$routeParameters->updateValues($requestPathParts)) {
			return false;
		}

		return true;
	}

	/**
	 * Whether the route & request path arrays exploded by '/' have the same count
	 * @param array $routePathParts
	 * @param array $requestPathParts
	 * @return bool False on failure
	 */
	private function matchPathPartCount(array $routePathParts, array $requestPathParts): bool
	{
		return count($routePathParts) === count($requestPathParts);
	}

	/**
	 * Whether the request path parts that are not marked as parameters in the route object have the same values as route ones
	 * @param string[] $routePathParts
	 * @param RouteParameterCollection $routeParameters
	 * @param string[] $requestPathParts
	 * @return bool False on failure
	 */
	private function matchNonParamParts(array $routePathParts, RouteParameterCollection $routeParameters, array $requestPathParts): bool
	{
		for ($i = 0; $i < count($requestPathParts); $i++) {
			if ($routeParameters->isParameterAtIndex($i)) {
				continue;
			}

			if ($routePathParts[$i] !== $requestPathParts[$i]) {
				return false;
			}
		}

		return true;
	}
}