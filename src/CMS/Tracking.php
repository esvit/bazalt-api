<?php

namespace CMS;

class Tracking
{
    public static function getRemoteIp()
    {
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ?
            (isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['HTTP_X_FORWARDED_FOR']) :
            (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1');//localhost for CLI mode

        if ($ip == '::1') {
            $ip = '127.0.0.1';
        }
        return $ip;
    }

    public static function getUserIp()
    {
        return sprintf('%u',ip2long(self::getRemoteIp()));
    }
}