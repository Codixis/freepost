<?php

namespace AppBundle\Utility;

class Crypto
{
    public static function sha512($string)
    {
        return hash("sha512", $string);
    }

    public static function randomString($base, $length)
    {
        if($length < 1)	return "";

        if($base < 1)		$base = 1;
        if($base > 62)	$base = 62;

        $srnd = '';
        $sbase = substr('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 0, $base);

        for($i = 0, --$base; $i < $length; $i++)
            $srnd .= $sbase[mt_rand(0, $base)];

        return $srnd;
    }

}
