<?php
declare(strict_types=1);

namespace Sdk;

use Sdk\Database\MariaDB\Connection;
use Sdk\Http\Entities\Cookie;
use Sdk\Http\Entities\RequestMethod;
use Sdk\Http\Entities\StatusCode;
use Sdk\Http\Request;
use Sdk\Http\Response;
use Sdk\Middleware\Entities\SessionVariable;
use Sdk\Middleware\Interfaces\IMiddleware;
use Sdk\Middleware\Redirect;
use Sdk\Middleware\Session;
use Sdk\Render\View;
use Sdk\Routing\Entities\Route;
use Sdk\Routing\Exceptions\RouteAlreadyExists;
use Sdk\Routing\Router;
use Sdk\Utils\Encryption\AES;
use Sdk\Utils\Hashing\Exceptions\InvalidPasswordAlgorithm;
use Sdk\Utils\Hashing\PasswordProvider;
use Sdk\Utils\Random;

final class App
{
    public readonly Router $router;
    private readonly Request $request;
    private Response $response;
    /**
     * @var IMiddleware[] $middleware
     */
    private array $middleware = [];

    /**
     * @throws InvalidPasswordAlgorithm
     */
    public function __construct(private readonly IConfig $config)
    {
        $this->request = new Request($this->config);
        $this->response = new Response();
        $this->router = new Router();

        $this->initAesEncryption();
        $this->initCookieEncryption();
        $this->initDefaultPasswordProvider();
        $this->spoofServerHeader();
        $this->initDatabaseConnection();
    }

    /**
     * Initializes the cookie encryption key
     */
    private function initCookieEncryption(): void
    {
        Cookie::setConfig($this->config);

        if ($this->config->isCookieEncryptionEnabled()) {
            if (!Session::isStarted()) {
                new Session($this->config)->execute($this->request, $this->response, []);
            }

            if (!Session::exists(SessionVariable::COOKIE_ENCRYPTION_KEY->value)) {
                Middleware\Session::set(SessionVariable::COOKIE_ENCRYPTION_KEY->value, AES::generateKey());
                Middleware\Session::set(SessionVariable::COOKIE_ENCRYPTION_IV->value, AES::generateKey());
            }
        }
    }

    private function initAesEncryption(): void
    {
        AES::setConfig($this->config);
    }

    /**
     * @throws InvalidPasswordAlgorithm
     */
    private function initDefaultPasswordProvider(): void
    {
        PasswordProvider::initDefaultProvider($this->config->getDefaultPasswordProviderHashAlgorithm(), $this->config->getDefaultPasswordProviderHashOptions());
    }

    private function spoofServerHeader(): void
    {
        if ($this->config->isSpoofedServerHeadEnabled()) {
            $this->response->addHeader('Server', $this->config->getSpoofedServerValue());
        }
    }

    private function initDatabaseConnection(): void
    {
        if ($this->config->isMariaDbEnabled()) {
            Connection::init($this->config->getMariaDbHost(), $this->config->getMariaDbUsername(), $this->config->getMariaDbPassword(), $this->config->getMariaDbDatabaseName());
        }
    }

    public function addMiddleware(IMiddleware $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

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

    private function runMiddleware(): void
    {
        foreach ($this->middleware as $middleware) {
            $this->response = $middleware->execute($this->request, $this->response, []);

            if ($this->response->getStatusCode() !== StatusCode::OK || $this->response->isLocationHeaderSent()) { //IF response status code is different from 200, we immediately send the response without any execution afterwards.
                $this->response->send();
            }
        }
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
    public function post(string $requestPathFormat, callable|string $callback, ?string $name = null): Route
    {
        return $this->route($requestPathFormat, $callback, RequestMethod::POST, $name);
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
    public function put(string $requestPathFormat, callable|string $callback, ?string $name = null): Route
    {
        return $this->route($requestPathFormat, $callback, RequestMethod::PUT, $name);
    }

    /**
     * @throws RouteAlreadyExists
     */
    public function redirect(string $from, string $to, ?string $name = null): Route
    {
        //only needed because of shit design I built this "framework" with...
        $dummyCallback = function(Request $request, Response $response, array $args): Response
        {
            return $response;
        };

        $redirectMiddleware = new Redirect($to);
        return $this->route($from, $dummyCallback, RequestMethod::getAllMethodsAsArray(), $name)
            ->addMiddleware($redirectMiddleware);
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

    /**
     * @throws RouteAlreadyExists
     */
    public function get(string $requestPathFormat, callable|string $callback, ?string $name = null): Route
    {
        return $this->route($requestPathFormat, $callback, RequestMethod::GET, $name);
    }
}