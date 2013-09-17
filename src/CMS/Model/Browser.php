<?php

namespace CMS\Model;

class Browser extends Base\Browser
{
    public static function create()
    {
        $browser = new Browser();
        $browser->is_bot = 0;
        return $browser;
    }

    public static function getUserAgent()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        }
        return 'unknown';
    }

    public static function getUserBrowser()
    {
        $q = \Bazalt\ORM::select('CMS\\Model\\Browser b')
            ->where('b.useragent = ?', self::getUserAgent())
            ->noCache();

        $browser = $q->fetch();
        if (!$browser) {
            $browser = Browser::create();
            $browser->useragent = self::getUserAgent();
            $browser->save();
        }
        return $browser;
    }
}