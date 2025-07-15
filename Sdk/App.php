<?php
declare(strict_types=1);

namespace Sdk;

use Exception;
use Sdk\Database\MariaDB\Connection;
use Sdk\Http\Entities\Cookie;
use Sdk\Http\Entities\RequestMethod;
use Sdk\Http\Entities\StatusCode;
use Sdk\Http\Request;
use Sdk\Http\Response;
use Sdk\Middleware\Entities\SessionVariable;
use Sdk\Middleware\Interfaces\IMiddleware;
use Sdk\Middleware\Logging;
use Sdk\Middleware\Redirect;
use Sdk\Middleware\Session;
use Sdk\Render\View;
use Sdk\Routing\Entities\Route;
use Sdk\Routing\Exceptions\RouteAlreadyExists;
use Sdk\Routing\Router;
use Sdk\Utils\Encryption\AES;
use Sdk\Utils\Hashing\Exceptions\InvalidPasswordAlgorithm;
use Sdk\Utils\Hashing\PasswordProvider;

final class App
{
    public readonly Router $router;
    private readonly Request $request;
    private Response $response;
    private Logging $logger;
    /**
     * @var IMiddleware[] $middleware
     */
    private array $middleware = [];

    public function __construct(private readonly IConfig $config)
    {
        $this->request = new Request($this->config);
        $this->response = new Response();
        $this->router = new Router();
        $this->logger = new Logging($this->config);

        try {
            Logging::logMessage("Initializing application", "App");

            $this->initAesEncryption();
            $this->initCookieEncryption();
            $this->initDefaultPasswordProvider();
            $this->spoofServerHeader();
            $this->initDatabaseConnection();
        } catch (Exception $e) {
            $this->logger->logException($this->request, $this->response, [], $e);
        }
    }

    private function initAesEncryption(): void
    {
        Logging::logMessage("Initializing AES", "Encryption");
        AES::setConfig($this->config);
    }

    /**
     * Initializes the cookie encryption key
     * @throws Exception
     */
    private function initCookieEncryption(): void
    {
        Logging::logMessage("Initializing cookie encryption", "Encryption");
        Cookie::setConfig($this->config);

        if ($this->config->isCookieEncryptionEnabled()) {
            if (!Session::isStarted()) {
                Logging::logMessage("Session not started, initializing session middleware", "Encryption");
                new Session($this->config)->execute($this->request, $this->response, []);
            }

            if (!Session::exists(SessionVariable::COOKIE_ENCRYPTION_KEY->value)) {
                Logging::logMessage("Generating cookie encryption AES key", "Encryption");
                Middleware\Session::set(SessionVariable::COOKIE_ENCRYPTION_KEY->value, AES::generateKey());
            }

            if (!Session::exists(SessionVariable::COOKIE_ENCRYPTION_IV->value)) {
                Logging::logMessage("Generating cookie encryption AES IV", "Encryption");
                Middleware\Session::set(SessionVariable::COOKIE_ENCRYPTION_IV->value, AES::generateIV());
            }
        }
    }

    /**
     * @throws InvalidPasswordAlgorithm
     */
    private function initDefaultPasswordProvider(): void
    {
        Logging::logMessage("Initializing default PasswordProvider", "Security");
        PasswordProvider::initDefaultProvider($this->config->getDefaultPasswordProviderHashAlgorithm(), $this->config->getDefaultPasswordProviderHashOptions());
    }

    private function spoofServerHeader(): void
    {
        if ($this->config->isSpoofedServerHeadEnabled()) {
            Logging::logMessage("Spoofing server header enabled, spoofing.", "Security");
            $this->response->addHeader('Server', $this->config->getSpoofedServerValue());
        }
    }

    private function initDatabaseConnection(): void
    {
        if ($this->config->isMariaDbEnabled()) {
            Logging::logMessage("MariaDB connection enabled, connecting to: " . $this->config->getMariaDbHost(), "MariaDB");
            Connection::init($this->config->getMariaDbHost(), $this->config->getMariaDbUsername(), $this->config->getMariaDbPassword(), $this->config->getMariaDbDatabaseName());
        }
    }

    /**
     * @throws Exception
     */
    public function run(): never
    {
        Logging::logMessage("Application run invoked", "App");
        try {
            $this->runMiddleware();
            $matchedRoute = $this->router->matchRoute($this->request);

            if ($matchedRoute !== null) {
                Logging::logMessage("Route matched: " . $matchedRoute->requestPathFormat, "App");
                $this->request->setRoute($matchedRoute);
                $this->response = $matchedRoute->execute($this->request, $this->response);
            } else {
                $this->response->setStatusCode(StatusCode::NOT_FOUND);
            }

            $this->response->send();
        } catch (Exception $e) {
            $this->logger->logException($this->request, $this->response, [], $e);
        }
    }


    /**
     * @throws Exception
     */
    private function runMiddleware(): void
    {
        foreach ($this->middleware as $middleware) {
            Logging::logMessage("Running middleware: " . $middleware::class, "App");
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
        $dummyCallback = function (Request $request, Response $response, array $args): Response {
            return $response;
        };

        $redirectMiddleware = new Redirect($to);
        return $this->route($from, $dummyCallback, RequestMethod::getAllMethodsAsArray(), $name)
            ->addMiddleware($redirectMiddleware);
    }

    public function addMiddleware(IMiddleware $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
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