<?php

namespace Gazelle\Util;

class Db
{
    public static function enum_boolean($bool)
    {
        return $bool == true ? '1' : '0';
    }

    //Handles escaping
    public static function string($string, $DisableWildcards = false)
    {
        global $DB;
        //Escape
        $string = $DB->escape_str($string);
        //Remove user input wildcards
        if ($DisableWildcards) {
            $string = str_replace(['%', '_'], ['\%', '\_'], $string);
        }

        return $string;
    }

    public static function array($Array, $DontEscape = [], $Quote = false)
    {
        foreach ($Array as $Key => $Val) {
            if (!in_array($Key, $DontEscape)) {
                if ($Quote) {
                    $Array[$Key] = '\'' . \Gazelle\Util\Db::string(trim($Val)) . '\'';
                } else {
                    $Array[$Key] = \Gazelle\Util\Db::string(trim($Val));
                }
            }
        }
        return $Array;
    }
}
