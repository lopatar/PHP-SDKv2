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
use Sdk\Routing\Entities\Route;
use Sdk\Routing\Router;
use Sdk\Utils\Random;

final class App
{
	private readonly Request $request;
	private Response $response;

	private readonly Router $router;

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
			$this->response = $matchedRoute->execute($this->request, $this->response);
		} else {
			$this->response->setStatusCode(StatusCode::NOT_FOUND);
		}

		Connection::getMysqlConnection()?->close();

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

	public function get(string $requestPathFormat, callable|string $callback): Route
	{
		return $this->route($requestPathFormat, $callback, RequestMethod::GET);
	}

	/**
	 * @param RequestMethod|RequestMethod[] $requestMethod
	 */
	public function route(string $requestPathFormat, callable|string $callback, RequestMethod|array $requestMethod): Route
	{
		$route = new Route($requestPathFormat, $callback, $requestMethod);
		$this->router->addRoute($route);
		return $route;
	}
	
	public function post(string $requestPathFormat, callable|string $callback): Route
	{
		return $this->route($requestPathFormat, $callback, RequestMethod::POST);
	}

	public function put(string $requestPathFormat, callable|string $callback): Route
	{
		return $this->route($requestPathFormat, $callback, RequestMethod::PUT);
	}

	public function delete(string $requestPathFormat, callable|string $callback): Route
	{
		return $this->route($requestPathFormat, $callback, RequestMethod::DELETE);
	}

	public function options(string $requestPathFormat, callable|string $callback): Route
	{
		return $this->route($requestPathFormat, $callback, RequestMethod::OPTIONS);
	}

	public function patch(string $requestPathFormat, callable|string $callback): Route
	{
		return $this->route($requestPathFormat, $callback, RequestMethod::PATCH);
	}

	public function any(string $requestPathFormat, callable|string $callback): Route
	{
		return $this->route($requestPathFormat, $callback, RequestMethod::cases());
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
}