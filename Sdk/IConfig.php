<?php
declare(strict_types=1);

namespace Sdk;

use Sdk\Http\Entities\CookieSameSite;
use Sdk\Middleware\Session;
use Sdk\Utils\Encryption\AES;

interface IConfig
{
    /**
     * If false, we output {@see Exception::$message} thrown in {@see Route::execute()} to {@see Response::$text}
     * Recommended value is false
     */
    public function isProduction(): bool;

    /**
     * MySQL/MariDB DB configuration
     * Gets whether {@see App} should initialize the MariaDB connection
     * @see Connection, App::initDatabaseConnection()
     */
    public function isMariaDbEnabled(): bool;

    /**
     * MySQL/MariDB DB configuration
     * Gets the MariaDB server host
     * @see Connection, App::initDatabaseConnection()
     */
    public function getMariaDbHost(): string;

    /**
     * MySQL/MariDB DB configuration
     * Gets the MariaDB server username
     * @see Connection, App::initDatabaseConnection()
     */
    public function getMariaDbUsername(): string;

    /**
     * MySQL/MariDB DB configuration
     * Gets the MariaDB server password
     * @see Connection, App::initDatabaseConnection()
     */
    public function getMariaDbPassword(): string;

    /**
     * MySQL/MariDB DB configuration
     * Gets the MariaDB server DB name
     * @see Connection, App::initDatabaseConnection()
     */
    public function getMariaDbDatabaseName(): string;

    /**
     * Session configuration
     * Gets the session cookie name
     * @see Session
     */
    public function getSessionName(): string;

    /**
     * Session configuration
     * Gets the session lifetime in seconds
     * @see Session
     */
    public function getSessionLifetime(): int;

    /**
     * Session configuration
     * Gets whether session should use strict mode, recommended value is <code>true</code>
     * @see Session
     */
    public function isSessionStrictModeEnabled(): bool;

    /**
     * Session configuration
     * Gets the session cookie path, recommended value is <code>/</code>
     * @see Session
     */
    public function getSessionCookiePath(): string;

    /**
     * Session configuration
     * Gets whether the session cookie can be only transmitted over HTTP, recommended value is <code>true</code>
     * @see Session
     */
    public function isSessionCookieHttpOnly(): bool;

    /**
     * Session configuration
     * Gets the session cookie SameSite attribute, recommended value is </code>CookieSameSite::STRICT</code>
     * @see Session
     */
    public function getSessionCookieSameSite(): CookieSameSite;

    /**
     * CSRF protection configuration
     * Gets the CSRF token lifetime in seconds, recommended value is 600
     * @see CSRF
     */
    public function getCsrfTokenLifetime(): int;

    /**
     * Server header spoofing
     * Gets whether spoofing Server head is enabled
     * @see App::spoofServerHeader()
     */
    public function isSpoofedServerHeadEnabled(): bool;

    /**
     * Server header spoofing
     * Gets the spoofed server head value
     * @see App::spoofServerHeader()
     */
    public function getSpoofedServerValue(): string;

    /**
     * Cookie encryption
     * Gets whether cookies should be automatically encrypted/decrypted
     * <h1><b>If enabled, no session middleware is needed to be added!</b></h1>
     * @uses \Sdk\Utils\Encryption\AES, Session
     */
    public function isCookieEncryptionEnabled(): bool;

    /**
     * Default password hashing provider hash algorithm, {@see password_algos()}
     */
    public function getDefaultPasswordProviderHashAlgorithm(): string;

    /**
     * Default password hashing provider hash algorithm options
     */
    public function getDefaultPasswordProviderHashOptions(): array;

    /**
     * Default AES cipher to be used within the @see AES class, currently only AES-128/256-CBC is supported!!!!!
     */
    public function getDefaultAesCipher(): string;

    public function getLoggingPath(): string;
}