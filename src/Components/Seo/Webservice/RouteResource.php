<?php

namespace Components\Sites\Webservice;
use Bazalt\Rest\Response,
    Bazalt\Rest\Resource,
    Components\Seo\Model\Route,
    Components\Seo\Model\Page;

/**
 * RoutesResource
 *
 * @uri /seo/routes
 */
class RouteResource extends Resource
{
    /**
     * @method GET
     * @json
     */
    public function findRoute()
    {
        if (!isset($_GET['url'])) {
            return new Response(Response::BADREQUEST, ['url' => 'required']);
        }
        if (!isset($_GET['route'])) {
            return new Response(Response::BADREQUEST, ['route' => 'required']);
        }
        $res = [
            'title'         => null,
            'keywords'      => null,
            'description'   => null
        ];
        $routeName = urldecode($_GET['route']);
        $route = Route::getByName($routeName);
        if ($route) {
            $res['route'] = $route->toArray();
            $res['title'] = $route->title;
            $res['keywords'] = $route->keywords;
            $res['description'] = $route->description;
        } else {
            $route = Route::create();
            $route->name = $routeName;
            $route->save();
        }

        $url = urldecode($_GET['url']);
        $page = Page::getByUrl($url);
        if ($page) {
            $res['page'] = $page->toArray();
            $res['title']       = empty($page->title)       ? $res['title']         : $page->title;
            $res['keywords']    = empty($page->keywords)    ? $res['keywords']      : $page->keywords;
            $res['description'] = empty($page->description) ? $res['description']   : $page->description;
        }

        return new Response(Response::OK, $res);
    }

    /**
     * @method POST
     * @json
     */
    public function saveArticle()
    {
        $res = new SiteResource($this->app, $this->request);

        return $res->saveItem();
    }
}
