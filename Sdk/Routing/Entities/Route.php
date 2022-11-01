<?php
declare(strict_types=1);

namespace Sdk\Routing\Entities;

use App\Config;
use Exception;
use Sdk\App;
use Sdk\Http\Entities\RequestMethod;
use Sdk\Http\Entities\StatusCode;
use Sdk\Http\Request;
use Sdk\Http\Response;
use Sdk\Middleware\Interfaces\IMiddleware;
use Sdk\Routing\Exceptions\RouteUrlGenerationFailure;
use Sdk\Routing\RouteMatcher;

/**
 * Object that holds all the information about a route, provides handling of matching, middleware & callbacks
 * @uses \Sdk\Http\Entities\RequestMethod
 * @uses \Sdk\Http\Request
 * @uses \Sdk\Http\Response
 * @uses \Sdk\Routing\RouteMatcher
 * @uses \Sdk\Routing\Entities\RouteParameter
 */
final class Route
{
	/**
	 * @var RequestMethod[] $requestMethod
	 */
	public readonly array $requestMethod;
	private RouteParameterCollection $parameters;
	/**
	 * @var string[] $requestPathFormatParts
	 * Array of strings that contains the values after splitting {@see Route::$requestPathFormat} by '/'
	 */
	private array $requestPathFormatParts;
	/**
	 * @var IMiddleware[] $middleware
	 */
	private array $middleware = [];
	private $callback;

	/**
	 * You can define parameters by using {} within the pathFormat (/hi/{username})
	 *
	 * @param string $requestPathFormat Request path format the route should match (e. g. /home)
	 * @param RequestMethod|RequestMethod[] $requestMethod Routes can have multiple request methods
	 */
	public function __construct(public readonly string $requestPathFormat, public readonly Config $config, callable|string $callback, RequestMethod|array $requestMethod, public readonly ?string $name = null)
	{
		$this->callback = (is_callable($callback)) ? $callback : $this->buildCallable($callback);
		$this->requestPathFormatParts = explode('/', $this->requestPathFormat);
		$this->parameters = new RouteParameterCollection($this->setUpParameters());
		$this->requestMethod = (is_array($requestMethod)) ? $requestMethod : [$requestMethod];
	}

	/**
	 * Builds the controller name, if the callback passed was not a valid callable
	 * @param string $controllerName
	 * @return string
	 */
	private function buildCallable(string $controllerName): string
	{
		return "\App\Controllers\\$controllerName";
	}

	/**
	 * @return RouteParameter[]
	 */
	private function setUpParameters(): array
	{
		/**
		 * @var RouteParameter[] $parameters ;
		 */
		$parameters = [];

		for ($i = 0; $i < count($this->requestPathFormatParts); $i++) {
			$part = $this->requestPathFormatParts[$i];

			if (str_starts_with($part, '{') && str_ends_with($part, '}')) //parameters are enclosed in { }
			{
				$parameterName = substr($part, 1, -1); //to remove { and } (first and last character)
				$parameters[$i] = new RouteParameter($parameterName, $i, $this);
			}
		}

		return $parameters;
	}

	/**
	 * Method that returns whether the current {@see Request} objects matches the {@see Route} properties
	 * @param Request $request
	 * @return bool
	 */
	public function match(Request $request): bool
	{
		$routeMatcher = new RouteMatcher($this, $request);

		if (!$routeMatcher->matchRequestMethod()) {
			return false;
		}

		if (!$this->hasParameters()) {
			return $routeMatcher->matchPlain();
		}

		return $routeMatcher->matchParameters($this->requestPathFormatParts, $this->parameters);
	}

	public function hasParameters(): bool
	{
		return $this->parameters->count() > 0;
	}

	/**
	 * Returns the {@see RouteParameter} object, by name from the {@see Route::$requestPathFormat}
	 * @param string $name
	 * @return RouteParameter|null Null if not found
	 */
	public function whereParam(string $name): ?RouteParameter
	{
		return $this->parameters->getParamByName($name);
	}

	public function addMiddleware(IMiddleware $middleware): self
	{
		$this->middleware[] = $middleware;
		return $this;
	}

	/**
	 * @param IMiddleware[] $middleware
	 */
	public function addMiddlewareBulk(array $middleware): self
	{
		$this->middleware = array_merge($this->middleware, $middleware);
		return $this;
	}

	/**
	 * @param array $parameters
	 * @param bool $validateValues Whether this function should value parameter values that the {@see RouteParameter route parameters have}
	 * @return string
	 * @throws RouteUrlGenerationFailure
	 */
	public function generateUrl(array $parameters, bool $validateValues = true): string
	{
		if (!$this->hasParameters()) {
			return $this->requestPathFormat;
		}

		foreach ($this->parameters as $routeParameter) {
			if (!isset($parameters[$routeParameter->name])) {
				throw new RouteUrlGenerationFailure("Parameters passed dont contain the following parameter: $routeParameter->name");
			}

			$paramValue = $parameters[$routeParameter->name];

			if ($validateValues && !$routeParameter->validateValue($paramValue)) {
				throw new RouteUrlGenerationFailure("Value: $paramValue does not pass the constraints of parameter: $routeParameter->name");
			}

			$parametersArray[$routeParameter->formatIndex] = $paramValue;
		}

		return '/'. implode('/', $parametersArray); //static analysis complains about not being (probably) defined. However, all edge cases are covered (hope so, haha.)
	}

	/**
	 * If the callback is not found {@see StatusCode NOT_IMPLEMENTED} response code is sent
	 * @param Response $response Initial response after running {@see App} middleware
	 * @return Response Response after running route middleware & callback
	 */
	public function execute(Request $request, Response $response): Response
	{
		$paramsAssoc = $this->parameters->getAssoc();
		try {
			$response = $this->runMiddleware($request, $response, $paramsAssoc);

			if ($response->getStatusCode() !== StatusCode::OK) { //IF response status code is different from 200, we immediately send the response without any execution afterwards.
				$response->send();
			}

			return call_user_func_array($this->callback, [$request, $response, $this->parameters->getAssoc()]);
		} catch (Exception $e) {
			$response->setStatusCode(StatusCode::INTERNAL_SERVER_ERROR);
			$response->writeLine(($this->config::IS_PRODUCTION) ? 'Internal server error occurred, please try again later.' : $e->getMessage());
			return $response;
		}
	}

	private function runMiddleware(Request $request, Response $response, array $args): Response
	{
		foreach ($this->middleware as $middleware) {
			$response = $middleware->execute($request, $response, $args);
		}

		return $response;
	}
}