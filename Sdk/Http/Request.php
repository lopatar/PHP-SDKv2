<?php
declare(strict_types=1);

namespace Sdk\Http;

use App\Config;
use Sdk\Http\Entities\Cookie;
use Sdk\Http\Entities\RequestMethod;
use Sdk\Http\Entities\Url;

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
	private array $headers;
	private Url $url;

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
	 */
	public function getCookie(string $name): ?Cookie
	{
		if (!$this->hasCookie($name)) {
            return null;
        }

        $cookieValue = $_COOKIE[$name];
        return ($this->config::COOKIE_ENCRYPTION) ? Cookie::fromEncrypted($name, $cookieValue) : new Cookie($name, $cookieValue);
	}

	public function hasCookie(string $name): bool
	{
		return isset($_COOKIE[$name]);
	}

	/**
	 * @return Cookie[]
	 */
	public function getCookies(): array
	{
		/**
		 * @var Cookie[] $cookies
		 */
		$cookies = [];

		foreach ($_COOKIE as $name => $value) {
			$cookies[] = new Cookie($name, $value);
		}

		return $cookies;
	}

	/**
	 * Returns a new instance of the current instance with the header added, does not modify the current instance.
	 */
	public function withHeader(string $name, string $value): self
	{
		if (!$this->hasHeader($name)) {
			$this->headers[$name] = $value;
			$clone = clone $this;
			unset($this->headers[$name]);
			return $clone;
		}

		$backup = $this->headers[$name];

		//header exists, therefore if it's already an array, we append. else we create an array with original values & append
		if (is_array($backup)) {
			$this->headers[$name][] = $value;
		} else {
			$this->headers[$name] = [$backup, $value];
		}

		$clone = clone $this;
		$this->headers[$name] = $backup;
		return $clone;
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