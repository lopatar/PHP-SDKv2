<?php

namespace Sdk\Utils;

abstract class Utils
{
    public static function printLine(string $data): void
    {
        echo "$data\n";
    }
}