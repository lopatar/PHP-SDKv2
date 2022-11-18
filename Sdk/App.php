<?php
declare(strict_types=1);

namespace Sdk;

use App\Config;
use Sdk\Database\MariaDB\Connection;
use Sdk\Http\Entities\Cookie;
use Sdk\Http\Entities\RequestMethod;
use Sdk\Http\Entities\StatusCode;
use Sdk\Http\Request;
use Sdk\Http\Response;
use Sdk\Middleware\Entities\SessionVariable;
use Sdk\Middleware\Exceptions\SessionNotStarted;
use Sdk\Middleware\Interfaces\IMiddleware;
use Sdk\Middleware\Session;
use Sdk\Render\View;
use Sdk\Routing\Entities\Route;
use Sdk\Routing\Exceptions\RouteAlreadyExists;
use Sdk\Routing\Router;
use Sdk\Utils\Random;

final class App
{
	private readonly Request $request;
	private Response $response;

	public readonly Router $router;

	/**
	 * @var IMiddleware[] $middleware
	 */
	private array $middleware = [];

	public function __construct(private readonly Config $config)
	{
		$this->request = new Request($this->config);
		$this->response = new Response();
		$this->router = new Router();

		$this->initDatabaseConnection();
		$this->spoofServerHeader();
	}

	private function initDatabaseConnection(): void
	{
		if ($this->config::USE_MARIADB) {
			Connection::init($this->config::MARIADB_HOST, $this->config::MARIADB_USERNAME, $this->config::MARIADB_PASSWORD, $this->config::MARIADB_DB_NAME);
		}
	}

	private function spoofServerHeader(): void
	{
		if ($this->config::SPOOF_SERVER_HEADER) {
			$this->response->addHeader('Server', $this->config::SERVER_HEADER_VALUE);
		}
	}

	/**
	 * Method that executes the application, matches routes, runs middleware and invokes the route methods
	 * @throws SessionNotStarted
	 */
	public function run(): never
	{
		$this->runMiddleware();
		$matchedRoute = $this->router->matchRoute($this->request);

		if ($matchedRoute !== null) {
			$this->request->setRoute($matchedRoute);
			$this->response = $matchedRoute->execute($this->request, $this->response);
		} else {
			$this->response->setStatusCode(StatusCode::NOT_FOUND);
		}

		$this->response->send();
	}

	/**
	 * @throws SessionNotStarted
	 */
	private function runMiddleware(): void
	{
		foreach ($this->middleware as $middleware) {
			$this->response = $middleware->execute($this->request, $this->response, []);

			if ($this->response->getStatusCode() !== StatusCode::OK) { //IF response status code is different from 200, we immediately send the response without any execution afterwards.
				$this->response->send();
			}

			if ($middleware instanceof Session) {
				$this->initCookieEncryption();
			}
		}
	}

	/**
	 * Initializes the
	 * @throws SessionNotStarted
	 */
	private function initCookieEncryption(): void
	{
		Cookie::setConfig($this->config);

		if ($this->config::COOKIE_ENCRYPTION) {
			if (!Session::isStarted()) {
				throw new SessionNotStarted('\\Sdk\\App');
			}

			if (!Session::exists(SessionVariable::COOKIE_ENCRYPTION_KEY->value)) {
				Middleware\Session::set(SessionVariable::COOKIE_ENCRYPTION_KEY->value, Random::stringSafe(32));
			}
		}
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
	 * @throws RouteAlreadyExists
	 */
	public function get(string $requestPathFormat, callable|string $callback, ?string $name = null): Route
	{
		return $this->route($requestPathFormat, $callback, RequestMethod::GET, $name);
	}

	/**
	 * @param RequestMethod|RequestMethod[] $requestMethod
	 * @throws RouteAlreadyExists
	 */
	public function route(string $requestPathFormat, callable|string $callback, RequestMethod|array $requestMethod, ?string $name = null): Route
	{
		$route = new Route($requestPathFormat, $this->config, $callback, $requestMethod, $name);
		$this->router->addRoute($route);
		return $route;
	}

	/**
	 * @throws RouteAlreadyExists
	 */
	public function post(string $requestPathFormat, callable|string $callback, ?string $name = null): Route
	{
		return $this->route($requestPathFormat, $callback, RequestMethod::POST, $name);
	}

	/**
	 * @throws RouteAlreadyExists
	 */
	public function put(string $requestPathFormat, callable|string $callback, ?string $name = null): Route
	{
		return $this->route($requestPathFormat, $callback, RequestMethod::PUT, $name);
	}

	/**
	 * @throws RouteAlreadyExists
	 */
	public function delete(string $requestPathFormat, callable|string $callback, ?string $name = null): Route
	{
		return $this->route($requestPathFormat, $callback, RequestMethod::DELETE, $name);
	}

	/**
	 * @throws RouteAlreadyExists
	 */
	public function options(string $requestPathFormat, callable|string $callback, ?string $name = null): Route
	{
		return $this->route($requestPathFormat, $callback, RequestMethod::OPTIONS, $name);
	}

	/**
	 * @throws RouteAlreadyExists
	 */
	public function patch(string $requestPathFormat, callable|string $callback, ?string $name = null): Route
	{
		return $this->route($requestPathFormat, $callback, RequestMethod::PATCH, $name);
	}

	/**
	 * @throws RouteAlreadyExists
	 */
	public function any(string $requestPathFormat, callable|string $callback, ?string $name = null): Route
	{
		return $this->route($requestPathFormat, $callback, RequestMethod::cases(), $name);
	}

	/**
	 * Function that allows us to create GET routes that directly render a {@see View} object, no need to define a controller
	 * @param string $requestPathFormat
	 * @param string|View $view Either a {@see View} object or fileName
	 * @param string|null $name
	 * @return Route
	 * @throws RouteAlreadyExists
	 */
    public function view(string $requestPathFormat, string|View $view, ?string $name = null): Route
    {
        return $this->get($requestPathFormat, function (Request $request, Response $response, array $args) use ($view): Response {
            if ($view instanceof View) {
                $response->setView($view);
            } else {
                $response->createView($view);
            }
            return $response;
        }, $name);
    }
}