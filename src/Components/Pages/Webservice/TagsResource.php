<?php

namespace Components\Pages\Webservice;
use Bazalt\Rest\Response;
use Components\Pages\Model\Tag;

/**
 * PagesResource
 *
 * @uri /pages/tags
 */
class TagsResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getItems()
    {
        if (isset($_GET['alias'])) {
            $page = Tag::getByUrl($_GET['alias']);
            if (!$page) {
                return new Response(Response::NOTFOUND, 'Page with alias "' . $_GET['alias'] . '" not found');
            }
            return new Response(Response::OK, $page->toArray());
        }

        if (isset($_GET['q'])) {
            $collection = Tag::searchByTitle($_GET['q']);
        } else {

            $user = \Bazalt\Auth::getUser();
            if ($user->isGuest()) {
                return new \Bazalt\Rest\Response(403, 'Access denied');
            }
            $collection = Tag::getCollection();
        }

        if (!isset($_GET['page'])) {
            $_GET['page'] = 1;
        }
        if (!isset($_GET['count'])) {
            $_GET['count'] = 10;
        }

        $tags = $collection->getPage((int)$_GET['page'], (int)$_GET['count']);
        $res = [];
        foreach ($tags as $tag) {
            $item = $tag->toArray();

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
     * @method GET
     * @action cloud
     * @priority 10
     * @json
     *
     *

    TagsResource.get({ 'action': 'cloud' }, function(res) {
    $scope.popularTags = res;
    });

    <div class="tags-cloud">
    <div ng-repeat="row in popularTags">
    <a ng-repeat="tag in row" ng-click="toggleTag(tag)" class="tag" ng-class="'tag-' + tag.size" href="">
    <span>{{ tag.title }}</span>
    </a>
    </div>
    </div>
     */
    public function getCloud()
    {
        $collection = Tag::getPopularCollection();

        if (!isset($_GET['page'])) {
            $_GET['page'] = 1;
        }
        if (!isset($_GET['count'])) {
            $_GET['count'] = 10;
        }

        $tags = $collection->getPage((int)$_GET['page'], (int)$_GET['count']);
        $res = [];
        $total = 0;
        $values = [];
        foreach ($tags as $tag) {
            $total += $tag->quantity;
            $values [] = $tag->quantity;

            $item = $tag->toArray();

            $res [] = $item;
        }

        $minimumCount = min(array_values($values));
        $maximumCount = max(array_values($values));
        $spread = $maximumCount - $minimumCount;

        $spread == 0 && $spread = 1;

        $minFontSize = 1;
        $maxFontSize = 10;
        foreach ($res as &$tag) {
            $tag['size'] = round($minFontSize + ($tag['quantity'] - $minimumCount) * ($maxFontSize - $minFontSize) / $spread);
        }
        $cnt = (int)ceil(sqrt(count($tags)));
        $tagCloud = [];

        $n = 0;
        $iIndex = $jIndex = $cnt;
        $count = 0;
        while ($n < $cnt) {
            for ($i = 0; $i < $n; $i++) {
                @$tagCloud[$iIndex++][$jIndex] = $res[$count++];
            }
            for ($i = 0; $i < $n; $i++) {
                @$tagCloud[$iIndex][$jIndex++] = $res[$count++];
            }
            $n++;
            for ($i = 0; $i < $n; $i++) {
                @$tagCloud[$iIndex--][$jIndex] = $res[$count++];
            }
            for ($i = 0; $i < $n; $i++) {
                @$tagCloud[$iIndex][$jIndex--] = $res[$count++];
            }
            $n++;
        }
        ksort($tagCloud);

        return new Response(Response::OK, $tagCloud);
    }
    /**
     * @method POST
     * @json
    public function saveArticle()
    {
        $res = new PageResource($this->app, $this->request);

        return $res->saveItem();
    }
     */
}