<?php

//Useful: http://www.robtex.com/cnet/
class Proxy
{
    private static $allowedProxies = [
        //Opera Turbo (may include Opera-owned IP addresses that aren't used for Turbo, but shouldn't run much risk of exploitation)
        '64.255.180.*', //Norway
        '64.255.164.*', //Norway
        '80.239.242.*', //Poland
        '80.239.243.*', //Poland
        '91.203.96.*', //Norway
        '94.246.126.*', //Norway
        '94.246.127.*', //Norway
        '195.189.142.*', //Norway
        '195.189.143.*', //Norway
    ];

    public static function check($ip)
    {
        for ($i = 0, $il = count(self::$allowedProxies); $i < $il; ++$i) {
            //based on the wildcard principle it should never be shorter
            if (strlen($IP) < strlen(self::$allowedProxies[$i])) {
                continue;
            }

            //since we're matching bit for bit iterating from the start
            for ($j = 0, $jl = strlen($IP); $j < $jl; ++$j) {
                //completed iteration and no inequality
                if ($j == $jl - 1 && $IP[$j] === self::$allowedProxies[$i][$j]) {
                    return true;
                }

                //wildcard
                if (self::$allowedProxies[$i][$j] === '*') {
                    return true;
                }

                //inequality found
                if ($IP[$j] !== self::$allowedProxies[$i][$j]) {
                    break;
                }
            }
        }
        return false;
    }
}
