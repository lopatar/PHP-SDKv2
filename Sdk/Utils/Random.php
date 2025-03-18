<?php
declare(strict_types=1);

namespace Sdk\Utils;

use Exception;
use JetBrains\PhpStorm\Immutable;
use Random\Randomizer;

/**
 * Random class that implements the new PHP random APIs. We can safely use the base Randomizer class, because the default is the SecureEngine
 */
#[Immutable]
abstract class Random
{
    /**
     * Function that generates a cryptographically secure random string
     * @param int $length Length of the string, if lower than 2, it gets clamped to 2
     * @return string
     * @uses bin2hex(), Randomizer
     */
    public static function stringSafe(int $length): string
    {
        if ($length < 2) {
            $length = 2;
        }

        return bin2hex((new Randomizer)->getBytes($length / 2));
    }

    /**
     * Function that generates a cryptographically secure bytes
     * @param int $length
     * @return string
     * @uses Randomizer
     */
    public static function bytesSafe(int $length): string
    {
        return (new Randomizer)->getBytes($length);
    }

    /**
     * Function that generates cryptographically secure random floats
     * @param float $min If bigger than $max, it gets clamped to value of $max - 1
     * @param float $max
     * @return float
     * @uses Randomizer
     */
    public static function floatSafe(float $min, float $max): float
    {
        $min = self::clampMinNum($min, $max);
        return (new Randomizer)->getFloat($min, $max);
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
        return (new Randomizer)->getInt($min, $max);
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
}