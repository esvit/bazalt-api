<?php

namespace Components\Pages\Webservice;
use Bazalt\Rest\Response;
use Components\Pages\Model\Category;
use Components\Pages\Model\Page;

/**
 * PagesResource
 *
 * @uri /pages
 */
class PagesResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getItems()
    {
        if (isset($_GET['alias'])) {
            $page = Page::getByUrl($_GET['alias']);
            if (!$page) {
                return new Response(Response::NOTFOUND, 'Page with alias "' . $_GET['alias'] . '" not found');
            }
            return new Response(Response::OK, $page->toArray());
        }

        if (isset($_GET['q'])) {
            $collection = Page::searchByTitle($_GET['q']);
        } else {
            $category = null;
            if (isset($_GET['category_id'])) {
                $category = Category::getById((int)$_GET['category_id']);
                if (!$category) {
                    return new Response(Response::NOTFOUND, 'Category with id "' . $_GET['category_id'] . '" not found');
                }
            }

            $user = \Bazalt\Auth::getUser();
            if ($user->isGuest() && isset($_GET['admin'])) {
                return new \Bazalt\Rest\Response(403, 'Access denied');
            }
            $collection = Page::getCollection(($user->isGuest() || !isset($_GET['admin'])), $category);
        }

        $user = \Bazalt\Auth::getUser();
        if (!$user->isGuest() && $user->hasPermission('admin.access')) {
            $collection->andWhere('user_id = ?', $user->id);
        }

        if (!isset($_GET['page'])) {
            $_GET['page'] = 1;
        }
        if (!isset($_GET['count'])) {
            $_GET['count'] = 10;
        }

        $news = $collection->getPage((int)$_GET['page'], (int)$_GET['count']);
        $res = [];
        foreach ($news as $article) {
            $item = $article->toArray();

            if (isset($_GET['truncate']) && isset($item['body'])) {
                foreach ($item['body'] as $key => $value) {
                    $item['body'][$key] = truncate($value, (int)$_GET['truncate']);
                }
            }
            $res [] = $item;
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

    /**
     * @method POST
     * @json
     */
    public function saveArticle()
    {
        $res = new PageResource($this->app, $this->request);

        return $res->saveItem();
    }
}