<?php
declare(strict_types=1);

namespace Sdk\Middleware\Exceptions;

use Exception;
use Throwable;

final class SessionNotStarted extends Exception
{
	public function __construct(string $className, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Tried initializing $className, while no \\Sdk\\Middleware\\Session middleware was configured!", $code, $previous);
	}
}