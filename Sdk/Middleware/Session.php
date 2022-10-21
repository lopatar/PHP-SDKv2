<?php
declare(strict_types=1);

namespace Sdk\Middleware;

use App\Config;
use JetBrains\PhpStorm\Immutable;
use Sdk\Http\Request;
use Sdk\Http\Response;
use Sdk\Middleware\Interfaces\IMiddleware;

#[Immutable]
final class Session implements IMiddleware
{
	public function __construct(private readonly Config $config) {}

    /**
     * Returns the value of session variable, set using {@see Session::set()}
     * @param string $name
     * @return string|float|int|array|null Null if not found
     */
	public static function get(string $name): string|float|int|array|null
	{
		return $_SESSION[$name] ?? null;
	}

	/**
	 * Returns whether the session variable exists
	 * @param string $name
	 * @return bool
	 */
	public static function exists(string $name): bool
	{
		return isset($_SESSION[$name]);
	}

    /**
     * Sets a session variable
     * @param string $name
     * @param string|float|int|array $value
     * @return void
     */
	public static function set(string $name, string|float|int|array $value): void
	{
		if (self::isStarted()) {
			$_SESSION[$name] = $value;
		}
	}

	/**
	 * Returns whether a session has been started
	 * @return bool
	 */
	public static function isStarted(): bool
	{
		return isset($_SESSION);
	}

	public function execute(Request $request, Response $response, array $args): Response
	{
		if (!self::isStarted()) {
			session_start([
				'name' => $this->config::SESSION_NAME,
				'use_strict_mode' => $this->config::SESSION_STRICT_MODE,
				'cookie_path' => $this->config::SESSION_COOKIE_PATH,
				'cookie_lifetime' => $this->config::SESSION_LIFETIME,
				'cookie_httponly' => $this->config::SESSION_COOKIE_HTTPONLY,
				'cookie_samesite' => $this->config::SESSION_COOKIE_SAMESITE->value,
				'sid_length' => $this->config::SESSION_ID_LENGTH,
				'sid_bits_per_character' => $this->config::SESSION_ID_BITS_PER_CHAR,
				'cookie_secure' => $request->isHttps(),
				'use_cookies' => true,
				'use_only_cookies' => true,
			]);
		}

		session_regenerate_id(); //regenerate ID on each request, makes session stealing attacks nearly impossible
		return $response;
	}
}