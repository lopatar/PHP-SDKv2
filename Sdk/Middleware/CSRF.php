<?php
declare(strict_types=1);

namespace Sdk\Middleware;

use App\Config;
use Sdk\Http\Entities\RequestMethod;
use Sdk\Http\Entities\StatusCode;
use Sdk\Http\Request;
use Sdk\Http\Response;
use Sdk\Middleware\Entities\SessionVariable;
use Sdk\Middleware\Exceptions\SessionNotStarted;
use Sdk\Middleware\Interfaces\IMiddleware;
use Sdk\Utils\Random;

final class CSRF implements IMiddleware
{
	private static ?Config $_config = null;

	public function __construct(private readonly Config $config)
	{
		self::$_config = $this->config; //hacky workaround
	}

	/**
	 * This function should be used for outputting the HTML form input element for sending the CSRF token to server side
	 * @throws SessionNotStarted
	 * @uses CSRF::getToken()
	 */
	public static function getInputField(): string
	{
		$token = self::getToken();
		$inputName = SessionVariable::CSRF_TOKEN->value;
		return "<input type=\"hidden\" name=\"$inputName\" value=\"$token\" required>";
	}

	/**
	 * This function is responsible for validating the POSTed token
	 * @throws SessionNotStarted
	 * @uses Session::isStarted(), Request::getPost(), CSRF::generateToken(), CSRF::isValid()
	 */
	public function execute(Request $request, Response $response, array $args): Response
	{
		if (!Session::isStarted()) {
			throw new SessionNotStarted('\\Sdk\\Middleware\\CSRF');
		}

		$token = ($request->method === RequestMethod::POST) ? $request->getPost(SessionVariable::CSRF_TOKEN->value) : $request->getHeader(SessionVariable::CSRF_TOKEN->value);

		if (!$this->isValid($token)) {
			$response->setStatusCode(StatusCode::FORBIDDEN);
		} else {
			self::generateToken(); //generate another token after it was verified
		}

		return $response;
	}


	/**
	 * @throws SessionNotStarted
	 */
	private function isValid(string|null $token): bool
	{
		return self::getToken() === $token;
	}

	/**
	 * @throws SessionNotStarted
	 */
	private static function getToken(): string
	{
		if (!Session::isStarted()) {
			throw new SessionNotStarted('\\Sdk\\Middleware\\CSRF');
		}

		if (self::isExpired()) {
			return self::generateToken();
		}

		return Session::get(SessionVariable::CSRF_TOKEN->value);
	}

	private static function isExpired(): bool
	{
		$expires = Session::get(SessionVariable::CSRF_EXPIRES->value);
		return $expires === null || time() > $expires;
	}

	private static function generateToken(): string
	{
		$token = Random::stringSafe(48);
		Session::set(SessionVariable::CSRF_TOKEN->value, $token);
		Session::set(SessionVariable::CSRF_EXPIRES->value, time() + self::$_config::CSRF_TOKEN_LIFETIME);
		return $token;
	}
}