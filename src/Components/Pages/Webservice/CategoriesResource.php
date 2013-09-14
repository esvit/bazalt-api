<?php

namespace Components\Pages\Webservice;

use \Bazalt\Rest\Response,
    \Bazalt\Data as Data;
use Components\Pages\Model\Category;

/**
 * @uri /pages/categories
 */
class CategoriesResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @provides application/json
     * @json
     * @throws \Exception
     * @return \Bazalt\Rest\Response
     */
    public function getElements()
    {
        $user = \Bazalt\Auth::getUser();
        if (isset($_GET['alias'])) {
            $category = Category::getByUrl($_GET['alias']);
            if (!$category) {
                return new Response(Response::NOTFOUND, 'Category with alias "' . $_GET['alias'] . '" not found');
            }
            return new Response(Response::OK, $category->toArray());
        }
        if (isset($_GET['q'])) {
            $collection = Category::searchByTitle($_GET['q']);

            if (!isset($_GET['page'])) {
                $_GET['page'] = 1;
            }
            if (!isset($_GET['count'])) {
                $_GET['count'] = 10;
            }
            $news = $collection->getPage((int)$_GET['page'], (int)$_GET['count']);

            $res = [];
            foreach ($news as $article) {
                $res [] = $article->toArray();
            }
            $data = [
                'data' => $res,
                'pager' => [
                    'current'       => $collection->page(),
                    'count'         => $collection->getPagesCount(),
                    'total'         => $collection->count(),
                    'countPerPage'  => $collection->countPerPage()
                ]
            ];
            return new Response(Response::OK, $data);
        }
        $category = Category::getSiteRootCategory();
        if (!$category) {
            throw new \Exception('Menu not found');
        }
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        return new Response(200, $category->toArray());
    }
}