<?php
declare(strict_types=1);

namespace Sdk\Utils;

use Exception;

abstract class Random
{
	/**
	 * Function that generates a cryptographically secure random string
	 * @param int $length Length of the string, if lower than 2, it gets clamped to 2
	 * @return string
	 * @uses bin2hex(), Random::bytesSafe()
	 */
	public static function stringSafe(int $length): string
	{
		if ($length < 2) {
			$length = 2;
		}

		return bin2hex(self::bytesSafe($length / 2));
	}

	/**
	 * Function that generates a cryptographically secure bytes
	 * @param int $length
	 * @return string
	 */
	public static function bytesSafe(int $length): string
	{
		return openssl_random_pseudo_bytes($length);
	}

	/**
	 * Function that generates cryptographically secure random floats
	 * @param float $min If bigger than $max, it gets clamped to value of $max - 1
	 * @param float $max
	 * @param int $decimals If lower than 1, clamped to 1, if higher than 14, clamped to 14
	 * @return float
	 * @throws Exception
	 * @uses Random::floatUnsafe(), Random::intSafe(), mt_srand()
	 * @see
	 */
	public static function floatSafe(float $min, float $max, int $decimals = 1): float
	{
		mt_srand(self::intSafe(PHP_INT_MIN, PHP_INT_MAX));
		return self::floatUnsafe($min, $max, $decimals);
	}

	/**
	 * Function that generates a cryptographically secure random integers
	 * @param int $min If bigger than $max, it gets clamped to value of $max - 1
	 * @param int $max
	 * @return int
	 * @throws Exception If no cryptographically secure source of randomness found
	 * @uses random_int(), Random::clampMinNum()
	 */
	public static function intSafe(int $min, int $max): int
	{
		$min = self::clampMinNum($min, $max);

		return random_int($min, $max);
	}

	/**
	 * Function that clamps the minimum value of random functions if the $min value is higher than $max to $min = $max - 1
	 * @param int|float $min
	 * @param int|float $max
	 * @return int|float
	 */
	private static function clampMinNum(int|float $min, int|float $max): int|float
	{
		return ($min > $max) ? $max - 1 : $min;
	}

	/**
	 * Function that generates NON CRYPTO SECURE random floats
	 * @param float $min If bigger than $max, it gets clamped to value of $max - 1
	 * @param float $max
	 * @param int $decimals If lower than 1, clamped to 1, if higher than 14, clamped to 14
	 * @return float
	 * @see https://www.php.net/manual/en/function.mt-getrandmax.php
	 * @uses mt_rand(), mt_getrandmax(), Random::clampMinNum(), Random::clampDecimals()
	 */
	public static function floatUnsafe(float $min, float $max, int $decimals = 1): float
	{
		$min = self::clampMinNum($min, $max);
		$decimals = self::clampDecimals($decimals);

		$value = $min + mt_rand() / mt_getrandmax() * ($max - $min);
		return round($value, $decimals);
	}

	private static function clampDecimals(int $decimals): int
	{
		return ($decimals < 1) ? 1 : (($decimals > 14) ? 14 : $decimals); //If lower than 1, clamped to 1, if higher than 14, clamped to 14
	}
}