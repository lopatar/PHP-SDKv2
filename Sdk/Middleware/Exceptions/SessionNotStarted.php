<?php
declare(strict_types=1);

namespace Sdk\Middleware\Exceptions;

use Throwable;

final class CSRFSessionNotStarted extends \Exception
{
	public function __construct(int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct('Tried initializing CSRF middleware, while no \\Sdk\\Middleware\\Session middleware was configured!', $code, $previous);
	}
}