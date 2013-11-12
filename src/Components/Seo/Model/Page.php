<?php

namespace Components\Seo\Model;

use Bazalt\ORM;

class Page extends Base\Page
{
    public static function create()
    {
        $route = new Page();
        $route->site_id = \Bazalt\Site::getId();
        return $route;
    }

    public static function getByUrl($url)
    {
        $q = Page::select()
            ->where('(site_id IS NULL OR site_id = ?)', \Bazalt\Site::getId())
            ->andWhere('url = ?', $url)
            ->orderBy('site_id DESC');

        return $q->fetch();
    }
}