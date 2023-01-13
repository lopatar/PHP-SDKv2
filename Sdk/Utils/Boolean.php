<?php

namespace Sdk\Utils;

abstract class Boolean
{
	/**
	 * This function accepts a string value that is then being converted to bool, acceptable values are 'true' and 'false'
	 * @param string $value
	 * @return bool|null Null on failure
	 */
	public static function fromString(string $value): ?bool
	{
		if ($value === 'true') {
			return true;
		}

		if ($value === 'false') {
			return false;
		}

		return null;
	}
}