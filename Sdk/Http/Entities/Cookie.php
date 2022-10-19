<?php
declare(strict_types=1);

namespace Sdk\Http\Entities;

use JetBrains\PhpStorm\Immutable;
use Sdk\Http\Request;

//TODO: Cookie encryption
#[Immutable]
final class Cookie
{
	public function __construct(public readonly string $name, public readonly string $value) {}

	/**
	 * This method creates the {@see Cookie} object and calls the {@see Cookie::create()} method
	 * @param string $name
	 * @param string $value
	 * @param Request $request
	 * @param int $expires Number of seconds the cookie should live for
	 * @param string $path
	 * @param string $domain Domain name, defaults to {@see DomainName::$domain}
	 * @param bool $httpOnly
	 * @param CookieSameSite $sameSite
	 * @return $this
	 */
	public static function set(string $name, string $value, Request $request, int $expires = 0, string $path = '/', string $domain = '', bool $httpOnly = true, CookieSameSite $sameSite = CookieSameSite::STRICT): self
	{
		return (new self($name, $value))->create($request, $expires, $path, $domain, $httpOnly, $sameSite);
	}

	/**
	 * @param Request $request
	 * @param int $expires Number of seconds the cookie should live for
	 * @param string $path
	 * @param string $domain Domain name, defaults to {@see DomainName::$domain}
	 * @param bool $httpOnly
	 * @param CookieSameSite $sameSite
	 * @return $this
	 */
	public function create(Request $request, int $expires = 0, string $path = '/', string $domain = '', bool $httpOnly = true, CookieSameSite $sameSite = CookieSameSite::STRICT): self
	{
		setcookie($this->name, $this->value, [
			'expires' => ($expires === 0) ? 0 : time() + $expires,
			'path' => $path,
			'domain' => ($domain === '') ? $request->getUrl()->domainName->domain : $domain,
			'secure' => $request->isHttps(),
			'httponly' => $httpOnly,
			'samesite' => $sameSite->value
		]);

		return $this;
	}

	public function remove(): void
	{
		unset($_COOKIE[$this->name]);
		setcookie($this->name, '', time() - 3600);
	}

	/**
	 * @param string $name
	 * @return Cookie
	 * Returns a new instance of the current object with the name modified, does not modify the current object, DOES NOT SET THE COOKIE
	 */
	public function withName(string $name): self
	{
		return new self($name, $this->value);
	}

	/**
	 * @param string $value
	 * @return $this
	 * Returns a new instance of the current object with the value modified, does not modify the current object.
	 */
	public function withValue(string $value): self
	{
		return new self($this->name, $value);
	}
}