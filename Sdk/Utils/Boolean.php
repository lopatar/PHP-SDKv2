<?php

namespace Sdk\Utils;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
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

    /**
     * This function turns a bool value into a string
     * @param bool $value
     * @return string
     */
    public static function toString(bool $value): string
    {
        return ($value) ? 'true' : 'false';
    }
}