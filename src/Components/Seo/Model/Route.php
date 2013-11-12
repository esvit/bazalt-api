<?php

namespace Components\Seo\Model;

use Bazalt\ORM;

class Route extends Base\Route
{
    public static function create()
    {
        $route = new Route();
        $route->site_id = \Bazalt\Site::getId();
        return $route;
    }

    public static function getByName($name)
    {
        $q = Route::select()
            ->where('(site_id IS NULL OR site_id = ?)', \Bazalt\Site::getId())
            ->andWhere('name = ?', $name)
            ->orderBy('site_id DESC')
            ->limit(1);

        return $q->fetch();
    }
}