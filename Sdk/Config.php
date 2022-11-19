<?php
declare(strict_types=1);

namespace Sdk;

use App\Models\ShortenedLink;
use Exception;
use JetBrains\PhpStorm\Immutable;
use Sdk\Database\MariaDB\Connection;
use Sdk\Http\Entities\CookieSameSite;
use Sdk\Middleware\CSRF;
use Sdk\Middleware\Session;

/**
 * Class that handles the {@see App} configuration
 */
#[Immutable]
abstract class Config
{
	/**
	 * If false, we output {@see Exception::$message} thrown in {@see Route::execute()} to {@see Response::$text}
	 */
	const IS_PRODUCTION = true;

	/**
	 * MySQL/MariDB DB configuration
	 * @see Connection, App::initDatabaseConnection()
	 */
	const USE_MARIADB = true;
	const MARIADB_HOST = '';
	const MARIADB_USERNAME = '';
	const MARIADB_PASSWORD = '';
	const MARIADB_DB_NAME = '';

	/**
	 * Session configuration
	 * @see Session
	 */
	const SESSION_NAME = 'SDK_SESSION';
	/**
	 * @see https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-lifetime
	 */
	const SESSION_LIFETIME = 3600;
	const SESSION_STRICT_MODE = true;
	const SESSION_COOKIE_PATH = '/';
	const SESSION_COOKIE_HTTPONLY = true;
	const SESSION_COOKIE_SAMESITE = CookieSameSite::STRICT;
	const SESSION_ID_LENGTH = 64;
	const SESSION_ID_BITS_PER_CHAR = 6;

	/**
	 * CSRF protection configuration
	 * @see CSRF
	 */
	const CSRF_TOKEN_LIFETIME = 600;

	/**
	 * Server header spoofing
	 * @see App::spoofServerHeader()
	 */
	const SPOOF_SERVER_HEADER = false;
	const SERVER_HEADER_VALUE = 'openresty';

	/**
	 * Cookie encryption
	 * @uses \Sdk\Utils\Encryption\AES, \Sdk\Middleware\Session
	 */
	const COOKIE_ENCRYPTION = false;

	/**
	 * How long the url code is, MIN = 2, MAX = 32
	 * @see ShortenedLink::insert()
	 */
	const SHORTENED_URL_CODE_LENGTH = 8;
}