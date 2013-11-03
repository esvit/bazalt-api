<?php

namespace Components\Sites\Webservice;
use Bazalt\Rest\Response,
    Bazalt\Site\Model\Site;

/**
 * SitesResource
 *
 * @uri /sites
 */
class SitesResource extends \Bazalt\Rest\Resource
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
        $collection = Site::getCollection();
$collection->andWhere('id != 6');
        // table configuration
        $table = new \Bazalt\Rest\Collection($collection);
        $table->sortableBy('title')
              ->filterBy('title', function($collection, $columnName, $value) {
                  $collection->andWhere('`' . $columnName . '` LIKE ?', '%' . $value . '%');
              })
              ->sortableBy('user_id')->filterBy('user_id')
              ->sortableBy('created_at')
              ->sortableBy('is_published');


        $res = $table->fetch($_GET);

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
        $res = new SiteResource($this->app, $this->request);

        return $res->saveItem();
    }
}
