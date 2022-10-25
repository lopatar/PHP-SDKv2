<?php
declare(strict_types=1);

namespace Sdk\Http;

use App\Config;
use Sdk\App;
use Sdk\Http\Entities\Cookie;
use Sdk\Http\Entities\RequestMethod;
use Sdk\Http\Entities\Url;
use Sdk\Http\Exceptions\CookieDecryptFailed;
use Sdk\Middleware\Interfaces\IMiddleware;
use Sdk\Routing\Entities\Route;

/**
 * BROKE VERSIONING COMMIT
 * Class that gives user enough abstraction around the incoming HTTP request
 * @uses \Sdk\Http\Entities\RequestMethod
 * @uses \Sdk\Http\Entities\Cookie
 * @uses \Sdk\Http\Entities\Url
 */
final class Request
{
	public readonly RequestMethod $method;
	/**
	 * Contains the HTTP protocol version string (e.g. HTTP/1.1)
	 */
	public readonly string $protocol;
	/**
	 * Contains the HTTP protocol version number (e.g. 1, 1.1, 2, 3)
	 */
	public readonly string $protocolVersion;
	/**
	 * @var string[]|string[][]
	 * Associative array of all request headers, keys might be assigned to an array of values
	 */
	public readonly array $headers;
	private Url $url;

	/**
	 * @var Route|null Route associated with the current object, will be null for {@see App} middleware
	 */
	private ?Route $route = null;

	public function __construct(private readonly Config $config)
	{
		$this->method = RequestMethod::from($this->getServer('REQUEST_METHOD'));
		$this->protocol = $this->getServer('SERVER_PROTOCOL');
		$this->protocolVersion = substr($this->protocol, strpos($this->protocol, '/') + 1); //HTTP/1.1 (version num = 1.1)
		$this->url = new Url($this);

		$headers = getallheaders();
		$this->headers = ($headers === false) ? [] : $headers; //getallheaders() returns false on fail
	}

	/**
	 * @return string|null Null on failure
	 */
	public function getServer(string $name): string|null
	{
		return $_SERVER[$name] ?? null;
	}

	public function __clone(): void
	{
		$this->url = new Url($this); //repairs object reference from cloned object
	}

	/**
	 * @return string|string[]|null Null on failure
	 */
	public function getHeader(string $name): string|array|null
	{
		return $this->headers[$name] ?? null;
	}

	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * @return string|null Null on failure
	 */
	public function getGet(string $name): string|null
	{
		return $this->getUrl()->getParameter($name);
	}


	public function getUrl(): Url
	{
		return $this->url;
	}

	/**
	 * @return string|null Null on failure
	 */
	public function getPost(string $name): string|null
	{
		return $_POST[$name] ?? null;
	}

	/**
	 * @return string|null Null on failure
	 */
	public function getEnv(string $name): string|null
	{
		return $_ENV[$name] ?? null;
	}

	/**
	 * @return Cookie|null Null on failure
	 * @throws CookieDecryptFailed
	 */
	public function getCookie(string $name): ?Cookie
	{
		if (!$this->hasCookie($name)) {
			return null;
		}

		$cookieValue = $_COOKIE[$name];
		//only decrypt cookies if encryption is enabled & name differs from session cookie name
		return ($this->config::COOKIE_ENCRYPTION && $this->config::SESSION_NAME !== $name) ? Cookie::fromEncrypted($name, $cookieValue) : new Cookie($name, $cookieValue);
	}

	public function hasCookie(string $name): bool
	{
		return isset($_COOKIE[$name]);
	}

	/**
	 * @return Cookie[]
	 * @throws CookieDecryptFailed
	 */
	public function getCookies(): array
	{
		/**
		 * @var Cookie[] $cookies
		 */
		$cookies = [];

		foreach ($_COOKIE as $name => $value) {
			//only decrypt cookies if encryption is enabled & name differs from session cookie name
			$cookies[] = ($this->config::COOKIE_ENCRYPTION && $this->config::SESSION_NAME !== $name) ? Cookie::fromEncrypted($name, $value) : new Cookie($name, $value);
		}

		return $cookies;
	}

	/**
	 * DO NOT USE, INTERNAL USE ONLY
	 * @param Route $route
	 * @return $this
	 * @internal
	 */
	public function setRoute(Route $route): self
	{
		$this->route = $route;
		return $this;
	}

	/**
	 * Gets the route associated with the current {@see Request} object, will be null for {@see App} {@see IMiddleware middleware}
	 * @return Route|null
	 */
	public function getRoute(): ?Route
	{
		return $this->route;
	}

	public function hasHeader(string $name): bool
	{
		return isset($this->headers[$name]);
	}

	public function isHttps(): bool
	{
		return $this->getUrl()->isHttps();
	}

	public function isHttp2(): bool
	{
		return $this->protocolVersion === "2";
	}

	/**
	 * @see Request::isHttp3()
	 * Is an alias of Request::isHttp3()
	 * @noinspection SpellCheckingInspection
	 */
	public function isQuic(): bool
	{
		return $this->isHttp3();
	}

	public function isHttp3(): bool
	{
		return $this->protocolVersion === "3";
	}
}