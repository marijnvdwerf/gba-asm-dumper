<?php

namespace MarijnvdWerf\DisAsm;

use Exception;

class Assert
{
    public static function equals($actual, $expected)
    {
        if ($actual == $expected) {
            return;
        }

        throw self::makeException("Failed asserting that %s matches expected %s.", $actual, $expected);
    }

    public static function isInstanceOf ($object, $class)
    {
        if (get_class($object) == $class) {
            return;
        }

        throw self::makeException("Failed asserting that %s is an instance of %s.", $object, $class);
    }

    private static function makeException()
    {
        $args = func_get_args();
        return new Exception(vsprintf(array_shift($args), $args));
    }
}
