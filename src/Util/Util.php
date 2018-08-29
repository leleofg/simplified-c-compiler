<?php

namespace Compiler\Util;

class Util
{
    public static function isBlankSpace(string $char): bool
    {
        if($char == ' ' || $char == '\n' || $char == '\t') {
            return true;
        }

        return false;
    }

    public static function isDot(string $char): bool
    {
        if($char == '.') {
            return true;
        }

        return false;
    }
}