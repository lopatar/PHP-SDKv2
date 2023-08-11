<?php

namespace Sdk\Utils;

abstract class Strings
{
    /**
     * Converts a string to upper case, handles detection of encoding
     * @param string $value
     * @return string
     */
    public static function toUpper(string $value): string
    {
        return mb_strtoupper($value, mb_detect_encoding($value));
    }

    /**
     * Converts a string to lower case, handles detection of encoding
     * @param string $value
     * @return string
     */
    public static function toLower(string $value): string
    {
        return mb_strtolower($value, mb_detect_encoding($value));
    }
}