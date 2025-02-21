<?php
declare(strict_types=1);

namespace Sdk\Middleware;

use JetBrains\PhpStorm\Immutable;
use Sdk\Http\Entities\Cookie;
use Sdk\Http\Request;
use Sdk\Http\Response;
use Sdk\IConfig;
use Sdk\Middleware\Interfaces\IMiddleware;

#[Immutable]
final  class Session implements IMiddleware
{
    private static ?IConfig $_config = null;

    public function __construct(private readonly IConfig $config)
    {
        self::$_config = $this->config;
    }

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

    /**
     * Ends the current session, removes all data and deletes the cookie
     * @return void
     */
    public static function end(): void
    {
        if (self::isStarted()) {
            session_destroy();

            if (self::$_config !== null) {
                new Cookie(self::$_config->getSessionName(), 'destroy')->remove();
            }
        }
    }

    /**
     * Removes the variable from the session array
     * @param string $name
     * @return void
     */
    public static function remove(string $name): void
    {
        if (self::isStarted() && self::exists($name)) {
            unset($_SESSION[$name]);
        }
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
     * Clears all session data, session is still active
     * @return void
     */
    public static function clear(): void
    {
        session_unset();
    }

    public function execute(Request $request, Response $response, array $args): Response
    {
        if (!self::isStarted()) {
            session_start([
                'name' => $this->config->getSessionName(),
                'use_strict_mode' => $this->config->isSessionStrictModeEnabled(),
                'cookie_path' => $this->config->getSessionCookiePath(),
                'cookie_lifetime' => $this->config->getSessionLifetime(),
                'cookie_httponly' => $this->config->isSessionCookieHttpOnly(),
                'cookie_samesite' => $this->config->getSessionCookieSameSite()->value,
                'cookie_secure' => $request->isHttps(),
                'use_cookies' => true,
                'use_only_cookies' => true,
            ]);
        } else {
            session_regenerate_id(); //regenerate ID on each request, makes session stealing attacks nearly impossible
        }

        return $response;
    }
}