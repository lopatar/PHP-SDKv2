<?php
declare(strict_types=1);

namespace Sdk\Http\Entities;

use App\Config;
use Sdk\Http\Exceptions\CookieDecryptFailed;
use Sdk\Http\Request;
use Sdk\Middleware\Entities\SessionVariable;
use Sdk\Middleware\Session;
use Sdk\Utils\Encryption\AES;

final class Cookie
{
	private static ?Config $config = null;

	public function __construct(public readonly string $name, public readonly string $value) {}

	/**
	 * Static constructor to get the {@see Cookie} object from an encrypted value
	 * @throws CookieDecryptFailed
	 */
	public static function fromEncrypted(string $name, string $encryptedValue): self
	{
		$decryptedValue = AES::decryptString($encryptedValue, Session::get(SessionVariable::COOKIE_ENCRYPTION_KEY->value));

		if ($decryptedValue === null) {
			throw new CookieDecryptFailed($name, $encryptedValue);
		}

		return new self($name, $decryptedValue);
	}

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
	 * This function actually sends the cookie to the browser, no need to call this method when using {@see Cookie::set()}
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
		$cookieValue = (self::$config::COOKIE_ENCRYPTION) ? AES::encryptString($this->value, Session::get(SessionVariable::COOKIE_ENCRYPTION_KEY->value)) : $this->value;
		setcookie($this->name, $cookieValue, [
			'expires' => ($expires === 0) ? 0 : time() + $expires,
			'path' => $path,
			'domain' => ($domain === '') ? $request->getUrl()->domainName->domain : $domain,
			'secure' => $request->isHttps(),
			'httponly' => $httpOnly,
			'samesite' => $sameSite->value
		]);

		return $this;
	}

	/**
	 * DO NOT USE, gets set in {@see App::initCookieEncryption()}
	 * @param Config $config
	 * @return void
	 * @internal
	 */
	public static function setConfig(Config $config): void
	{
		self::$config = $config;
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