<?php
declare(strict_types=1);

namespace Sdk\Middleware;

use Sdk\Http\Entities\StatusCode;
use Sdk\Http\Request;
use Sdk\Http\Response;

/**
 * Middleware that allows for performing HTTP basic auth
 */
final class HttpBasicAuth implements Interfaces\IMiddleware
{
	/**
	 * @var array = [
	 *    0 => ['username' => 'username', 'password' => 'passwordHash'],
	 *    1 => ['username' => 'username2', 'password' => 'passwordHash2'],
	 * ] User credentials in the annotation format, passwords are meant to be protected using {@see password_hash() or @see}
	 */
	private readonly array $userCredentials;

	/**
	 * @param array $userCredentials = [
	 *    0 => ['username' => 'username', 'password' => 'passwordHash'],
	 *    1 => ['username' => 'username2', 'password' => 'passwordHash2'],
	 * ] User credentials in the annotation format, passwords are meant to be protected using {@see password_hash() or @see}
	 * @param string $httpRealm
	 */
	public function __construct(array $userCredentials, private readonly string $httpRealm = 'Protected resource')
	{
		//$userCredentials not passed as readonly in constructor because deep-assoc-completion does not support that!
		$this->userCredentials = $userCredentials;
	}


	public function execute(Request $request, Response $response, array $args): Response
	{
		$credentialsHeader = $request->getHeader('Authorization');

		if ($credentialsHeader === null) {

			return $this->buildAuthenticationResponse($response);
		}

		$credentialsHeaderParts = explode(' ', $credentialsHeader);

		//Only Basic method is allowed, also after splitting it should only contain 2 values
		if ($credentialsHeaderParts[0] !== 'Basic' || count($credentialsHeaderParts) !== 2) {
			return $this->buildAuthenticationResponse($response);
		}

		$credentialsString = base64_decode($credentialsHeaderParts[1]);

		if (!str_contains($credentialsString, ':')) {
			return $this->buildAuthenticationResponse($response);
		}

		$credentials = explode(':', $credentialsString);

		if (!$this->validateCredentials($credentials)) {
			return $this->buildAuthenticationResponse($response);
		}

		return $response;
	}

	private function buildAuthenticationResponse(Response $response): Response
	{
		$response->setStatusCode(StatusCode::UNAUTHORIZED);
		$response->addHeader('WWW-Authenticate', "Basic realm=\"$this->httpRealm\"");
		return $response;
	}

	/**
	 * @param array<int, string> $userCredentials
	 * @return bool
	 */
	private function validateCredentials(array $userCredentials): bool
	{
		foreach ($this->userCredentials as $credentialPair) {
			if ($userCredentials[0] !== $credentialPair['username']) {
				continue;
			}

			return password_verify($userCredentials[1], $credentialPair['password']);
		}

		return false;
	}
}