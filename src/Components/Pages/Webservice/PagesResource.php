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

        // table configuration
        $table = new \Bazalt\Rest\Collection($collection);
        $table->sortableBy('title')
              ->filterBy('title', function($collection, $columnName, $value) {
                  $collection->andWhere('`' . $columnName . '` LIKE ?', '%' . $value . '%');
              })
              ->sortableBy('user_id')->filterBy('user_id')
              ->sortableBy('created_at')
              ->sortableBy('is_published');

        $user = \Bazalt\Auth::getUser();
        if (!$user->isGuest() && $user->hasPermission('admin.access')) {
            $collection->andWhere('user_id = ?', $user->id);
        }

        $res = $table->fetch($_GET, function($item){
            if (isset($_GET['truncate']) && isset($item['body'])) {
                foreach ($item['body'] as $key => $value) {
                    $item['body'][$key] = truncate($value, (int)$_GET['truncate']);
                }
            }
            return $item;
        });

        return new Response(Response::OK, $res);
    }

    /**
     * @method GET
     * @action statistic
     * @json
     */
    public function getStatistic()
    {
        $begin = strtotime('-1 month +1 day');
        $end = time();

        $res = Page::getStatistic($begin, $end);

        $return = [
            'data'  => [],
            'users' => []
        ];
        foreach ($res as $item) {
            $res = [
                'count' => $item->cnt,
                'user' => 0
            ];
            if ($item->user_id) {
                $user = $item->User;
                $res['user'] = $user->id;
                $return['users'][$user->id] = $user->getName();
            }
            $return['data'][date('Y-m-d', strtotime($item->created_at))] = $res;
        }

        return new Response(Response::OK, $return);
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