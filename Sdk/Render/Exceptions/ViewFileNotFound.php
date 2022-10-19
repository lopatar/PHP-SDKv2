<?php
declare(strict_types=1);

namespace Sdk\Render\Exceptions;

use Throwable;

final class ViewFileNotFound extends \Exception
{
	public function __construct(string $filePath, int $code = 0, ?Throwable $previous = null)
	{
		$message = "View file at path $filePath not found!";
		parent::__construct($message, $code, $previous);
	}
}