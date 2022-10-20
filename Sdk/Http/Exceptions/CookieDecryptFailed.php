<?php

namespace Sdk\Http\Exceptions;

use Exception;
use Throwable;

final class CookieDecryptFailed extends Exception
{
	public function __construct(string $cookieName, string $cookieValue, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Failed decrypting cookie \"$cookieName\" with value of \"$cookieValue\"", $code, $previous);
	}
}