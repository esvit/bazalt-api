<?php

namespace Components\Widgets\Webservice;
use Bazalt\Rest\Response;

/**
 * WidgetsResource
 *
 * @uri /widgets
 */
class WidgetsResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getItems()
    {
        $user = \Bazalt\Auth::getUser();
        if ($user->isGuest()) {
            return new \Bazalt\Rest\Response(403, 'Access denied');
        }
        $info = $user->toArray();

        $collection = \Components\Widgets\Model\Widget::getCollection();
        if (isset($info['acl']['blog']) && $info['acl']['blog'] & 4) {

        } else {
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
            $res [] = $article->toArray();
        }
        $data = [
            'data' => $res,
            'pager' => [
                'current'           => $collection->page(),
                'count'         => $collection->getPagesCount(),
                'total'         => $collection->count(),
                'countPerPage'  => $collection->countPerPage()
            ]
        ];
        return new Response(Response::OK, $data);
    }

    /**
     * @method PUT
     * @json
     */
    public function saveItem()
    {
        $res = new WidgetResource($this->app, $this->request);

        return $res->saveItem();
    }
}