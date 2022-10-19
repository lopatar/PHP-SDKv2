<?php
declare(strict_types=1);

namespace Sdk\Middleware;

use App\Config;
use Sdk\Http\Entities\RequestMethod;
use Sdk\Http\Entities\StatusCode;
use Sdk\Http\Request;
use Sdk\Http\Response;
use Sdk\Middleware\Exceptions\CSRFSessionNotStarted;
use Sdk\Middleware\Interfaces\IMiddleware;
use Sdk\Utils\Random;

//TODO: FINISH, ADD CHECK METHODS (POST, COOKIE, X-CSRF-HEADER)
final class CSRF implements IMiddleware
{
	private static ?Config $_config = null;

	public function __construct(private readonly Config $config) {
		self::$_config = $this->config; //hacky workaround
	}

	/**
	 * This function is responsible for validating the POSTed token
	 * @throws CSRFSessionNotStarted
	 * @uses Session::isStarted(), Request::getPost(), CSRF::generateToken(), CSRF::isValid()
	 */
	public function execute(Request $request, Response $response, array $args): Response
	{
		if ($request->method !== RequestMethod::POST) { //if not a POST request, we continue without checking
			return $response;
		}

		if (!Session::isStarted()) {
			throw new CSRFSessionNotStarted();
		}

		$token = $request->getPost('csrfToken');

		if (!$this->isValid($token)) {
			$response->setStatusCode(StatusCode::FORBIDDEN);
		} else {
			self::generateToken(); //generate another token after it was verified
		}

		return $response;
	}

	/**
	 * This function should be used for outputting the HTML form input element for sending the CSRF token to server side
	 * @throws CSRFSessionNotStarted
	 * @uses Session::isStarted(), CSRF::getToken()
	 */
	public static function getInputField(): string
	{
		if (!Session::isStarted()) {
			throw new CSRFSessionNotStarted();
		}

		$token = self::getToken();
		return "<input type=\"hidden\" name=\"csrfToken\" value=\"$token\" required>";
	}

	/**
	 * @throws CSRFSessionNotStarted
	 */
	private function isValid(string|null $token): bool
	{
		return self::getToken() === $token;
	}

	/**
	 * @throws CSRFSessionNotStarted
	 */
	private static function getToken(): string
	{
		if (!Session::isStarted()) {
			throw new CSRFSessionNotStarted();
		}

		if (self::isExpired()) {
			return self::generateToken();
		}

		return Session::get('csrfToken');
	}

	private static function isExpired(): bool
	{
		$expires = Session::get('csrfExpires');
		return $expires === null || time() > $expires;
	}

	private static function generateToken(): string
	{
		$token = Random::stringSafe(48);
		Session::set('csrfToken', $token);
		Session::set('csrfExpires', time() + self::$_config::CSRF_TOKEN_LIFETIME);
		return $token;
	}
}