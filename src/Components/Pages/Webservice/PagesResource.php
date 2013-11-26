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
     *
     * @urlParam string alias - Если указан, то ищет страницу с заданым url
     * @urlParam int manage - Если указан, то возвращает неопубликованные страницы, которые доступны данному пользователю
     * @urlParam int truncate - Если указан, то обрезает поле body до заданой длины
     * @urlParam int|array category_id - Если указан 1 ид, то возращает страницы из этой категории и всех подкатегорий, если масив, то только заданные в масиве категории
     */
    public function getItems()
    {
        $params = $this->params();
        if (isset($params['alias'])) {
            $page = Page::getByUrl($params['alias'], true);
            if (!$page) {
                return new Response(Response::NOTFOUND, ['alias' => 'Page with alias "' . $params['alias'] . '" not found']);
            }
            return new Response(Response::OK, $page->toArray());
        }

        $user = \Bazalt\Auth::getUser();
        if ($user->isGuest() && isset($params['manage'])) {
            return new \Bazalt\Rest\Response(Response::FORBIDDEN, ['id' => 'Access denied']);
        }

        $categories = array();
        if (isset($params['category_id'])) {
            if (is_array($params['category_id'])) {
                $categories = $params['category_id'];
            } else {
                $category = Category::getById((int)$params['category_id']);
                if (!$category) {
                    return new Response(Response::NOTFOUND, ['category_id' => 'Category with id "' . $params['category_id'] . '" not found']);
                }
                $categories = [ $category->id ];
            }
        }
        $collection = Page::getCollection($categories);

        // table configuration
        $table = new \Bazalt\Rest\Collection($collection);
        $table->sortableBy('title')
              ->filterBy('title', function($collection, $columnName, $value) {
                  $collection->andWhere('`' . $columnName . '` LIKE ?', '%' . $value . '%');
              })
              ->filterBy('status', function($collection, $columnName, $value) {
                  $collection->andWhere('`' . $columnName . '` = ?', $value);
              })
              ->sortableBy('user_id')->filterBy('user_id')
              ->sortableBy('is_top')->filterBy('is_top')
              ->sortableBy('created_at')
              ->sortableBy('status');

        $user = \Bazalt\Auth::getUser();
        if (isset($params['manage'])) {// && !$user->isGuest() && $user->hasPermission('admin.access')) {
            $collection->andWhere('user_id = ?', $user->id);
        } else {
            $collection->andWhere('status >= ?', Page::PUBLISH_STATE_PUBLISHED);
        }

        $res = $table->fetch($params, function($item) use ($params) {
            if (isset($params['truncate']) && isset($item['body'])) {
                foreach ($item['body'] as $key => $value) {
                    $item['body'][$key] = nl2br(truncate($value, (int)$params['truncate']));
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