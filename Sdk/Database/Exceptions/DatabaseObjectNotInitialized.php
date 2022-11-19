<?php
declare(strict_types=1);

namespace Sdk\Database\Exceptions;

use Throwable;

final class DatabaseObjectNotInitialized extends \Exception
{
	public function __construct(string $name, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Cannot query while DB object is not initialized! [$name]", $code, $previous);
	}
}