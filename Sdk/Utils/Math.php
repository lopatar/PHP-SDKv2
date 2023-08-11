<?php

namespace Sdk\Utils;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
abstract class Math
{
    /**
     * This function always round the number up, php lacks such flag in {@see round()}
     * @param float $value
     * @param int $precision
     * @return float|int
     */
    public static function roundUp(float $value, int $precision): float|int
    {
        $fig = (int)str_pad('1', $precision, '0');
        return (ceil($value * $fig) / $fig);
    }

    /**
     * This function always round the number down, php lacks such flag in {@see round()}
     * @param float $value
     * @param int $precision
     * @return float|int
     */
    public static function roundDown(float $value, int $precision): float|int
    {
        $fig = (int)str_pad('1', $precision, '0');
        return (floor($value * $fig) / $fig);
    }
}