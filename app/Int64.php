<?php

namespace Gazelle;

/**
 * If we're running a 32bit PHP version, we use small objects to store ints.
 * Overhead from the function calls is small enough to not worry about
 */

class Int64
{
    private $Num;

    public function __construct($Val)
    {
        $this->Num = $Val;
    }

    public static function make($Val)
    {
        return PHP_INT_SIZE === 4 ? \Gazelle\Int64($Val) : (int)$Val;
    }

    public static function get($Val)
    {
        return PHP_INT_SIZE === 4 ? $Val->Num : $Val;
    }

    public static function is_int($Val)
    {
        return is_int($Val) || (is_object($Val) && get_class($Val) === 'Int64');
    }
}
