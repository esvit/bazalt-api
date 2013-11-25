<?php

namespace tests\Pages\Webservice;

use Bazalt\Rest;
use Components\Pages\Model\Page;
use Components\Pages\Model\Category;

class PagesResourceTest extends \tests\BaseCase
{
    protected $category;

    protected $category2;

    protected $pages;

    public function createPages($arr = array())
    {
        $pages = array();
        $i = 0;
        foreach ($arr as $item) {
            $page = Page::create();
            foreach ($item as $key => $param) {
                $page->{$key} = $param;
            }
            $page->id = $i++;
            $page->created_at = date('Y-m-d H:i:s', time() + $i);
            $page->updated_at = date('Y-m-d H:i:s', time() + $i);
            $page->save();
            $pages []= $page;
        }
        return $pages;
    }

    protected function setUp()
    {
        parent::setUp();

        $this->initApp(getWebServices());

        $this->category = Category::create();
        $this->category->rgt = 4;
        $this->category->save();
        $this->models []= $this->category;

        $this->category2 = Category::create();
        $this->category2->lft = 2;
        $this->category2->rgt = 3;
        $this->category2->save();
        $this->models []= $this->category2;

        $user = $this->createAdminUser();
        $this->pages = $this->createPages([
            [ // 0 Удаленная страница
                'status' => Page::PUBLISH_STATE_DELETED,
                'title' => ['en' => 'Page 1'],
                'template' => 'default.html',
                'is_allow_comments' => 1,
                'comments_count' => 0,
                'url' => 'test',
                'hits' => 0
            ],
            [ // 1 своя неопубликованая страница (черновик)
                'status' => Page::PUBLISH_STATE_DRAFT,
                'title' => ['en' => 'Page 2'],
                'template' => 'default.html',
                'is_allow_comments' => 1,
                'comments_count' => 0,
                'url' => 'test',
                'hits' => 0,
                'category_id' => $this->category->id
            ],
            [ // 2 свой черновик, который не прошел модерацию
                'status' => Page::PUBLISH_STATE_MODERATED,
                'title' => ['en' => 'Page 3'],
                'template' => 'default.html',
                'is_allow_comments' => 1,
                'comments_count' => 0,
                'url' => 'test',
                'hits' => 0,
                'category_id' => $this->category->id
            ],
            [ // 3 своя опубликованая страница
                'status' => Page::PUBLISH_STATE_PUBLISHED,
                'title' => ['en' => 'Page 4'],
                'template' => 'default.html',
                'is_allow_comments' => 1,
                'comments_count' => 0,
                'url' => 'test',
                'hits' => 0,
                'is_top' => 1,
                'category_id' => $this->category->id
            ],
            [ // 4 своя статья, которую еще не проверил модератор
                'status' => Page::PUBLISH_STATE_UPDATED,
                'title' => ['en' => 'Page 5'],
                'template' => 'default.html',
                'is_allow_comments' => 1,
                'comments_count' => 0,
                'hits' => 0,
                'category_id' => $this->category2->id
            ],
            [ // 5 чужая даленная страница
                'status' => Page::PUBLISH_STATE_DELETED,
                'user_id' => $user->id,
                'title' => ['en' => 'Page 1'],
                'template' => 'default.html',
                'is_allow_comments' => 1,
                'comments_count' => 0,
                'hits' => 0
            ],
            [ // 6 чужая неопубликованая страница (черновик)
                'status' => Page::PUBLISH_STATE_DRAFT,
                'user_id' => $user->id,
                'title' => ['en' => 'Page 2'],
                'template' => 'default.html',
                'is_allow_comments' => 1,
                'comments_count' => 0,
                'hits' => 0
            ],
            [ // 7 чужой черновик, который не прошел модерацию
                'status' => Page::PUBLISH_STATE_MODERATED,
                'user_id' => $user->id,
                'title' => ['en' => 'Page 3'],
                'template' => 'default.html',
                'is_allow_comments' => 1,
                'comments_count' => 0,
                'hits' => 0,
                'category_id' => $this->category->id
            ],
            [ // 8 чужая опубликованая страница
                'status' => Page::PUBLISH_STATE_PUBLISHED,
                'user_id' => $user->id,
                'title' => ['en' => 'Page 4'],
                'template' => 'default.html',
                'is_allow_comments' => 1,
                'comments_count' => 0,
                'hits' => 0,
                'category_id' => $this->category->id
            ],
            [ // 9 чужая статья, которую еще не проверил модератор
                'status' => Page::PUBLISH_STATE_UPDATED,
                'user_id' => $user->id,
                'title' => ['en' => 'Page 5'],
                'body' => ['en' => '1 2 3 4 5 6 7 9 10 11'],
                'template' => 'default.html',
                'is_allow_comments' => 1,
                'comments_count' => 0,
                'hits' => 0,
                'is_top' => 1,
                'category_id' => $this->category2->id
            ]
        ]);
    }

    protected function tearDown()
    {
        parent::tearDown();

        foreach ($this->pages as $page) {
            $page->delete();
        }
    }

    // пользователь запрашивает все опубликованные страницы
    public function testGetPages()
    {
        $response = new \Bazalt\Rest\Response(200, ['data' => [
            $this->pages[9]->toArray(),
            $this->pages[8]->toArray(),
            $this->pages[4]->toArray(),
            $this->pages[3]->toArray()
        ], 'pager' => [
            'current' => 1,
            'count' => 1,
            'total' => 4,
            'countPerPage' => 10
        ]]);
        $this->assertResponse('GET /pages/', [], $response);

        $response = new \Bazalt\Rest\Response(200, ['data' => [
            $this->pages[9]->toArray(),
            $this->pages[4]->toArray()
        ], 'pager' => [
            'current' => 1,
            'count' => 1,
            'total' => 2,
            'countPerPage' => 10
        ]]);
        $this->assertResponse('GET /pages/', ['data' => ['category_id' => $this->category2->id]], $response);
    }

    // /pages/?manage=1
    // пользователь запрашивает все свои страницы опубликованые, черновики и отклоненные
    public function testGetPagesWithSelfUnpublished()
    {
        $response = new \Bazalt\Rest\Response(200, ['data' => [
            $this->pages[4]->toArray(),
            $this->pages[3]->toArray(),
            $this->pages[2]->toArray(),
            $this->pages[1]->toArray()
        ], 'pager' => [
            'current' => 1,
            'count' => 1,
            'total' => 4,
            'countPerPage' => 10
        ]]);
        $this->assertResponse('GET /pages', ['data' => ['manage' => 1]], $response);

        $response = new \Bazalt\Rest\Response(200, ['data' => [
            $this->pages[4]->toArray(),
            $this->pages[3]->toArray(),
            $this->pages[2]->toArray(),
            $this->pages[1]->toArray()
        ], 'pager' => [
            'current' => 1,
            'count' => 1,
            'total' => 4,
            'countPerPage' => 10
        ]]);
        $this->assertResponse('GET /pages', ['data' => ['manage' => 1, 'category_id' => $this->category->id]], $response);

        $response = new \Bazalt\Rest\Response(200, ['data' => [
            $this->pages[4]->toArray()
        ], 'pager' => [
            'current' => 1,
            'count' => 1,
            'total' => 1,
            'countPerPage' => 10
        ]]);
        $this->assertResponse('GET /pages', ['data' => ['manage' => 1, 'category_id' => $this->category2->id]], $response);
    }

    // /pages/?filter[is_top]=1
    // пользователь запрашивает все свои страницы опубликованые, черновики и отклоненные
    public function testGetTopPages()
    {
        $response = new \Bazalt\Rest\Response(200, ['data' => [
            $this->pages[9]->toArray(),
            $this->pages[3]->toArray()
        ], 'pager' => [
            'current' => 1,
            'count' => 1,
            'total' => 2,
            'countPerPage' => 10
        ]]);
        $this->assertResponse('GET /pages', ['data' => ['filter' => ['is_top' => 1]]], $response);

        $response = new \Bazalt\Rest\Response(200, ['data' => [
            $this->pages[9]->toArray()
        ], 'pager' => [
            'current' => 1,
            'count' => 1,
            'total' => 1,
            'countPerPage' => 10
        ]]);
        $this->assertResponse('GET /pages', ['data' => ['category_id' => $this->category2->id, 'filter' => ['is_top' => 1]]], $response);
    }

    // /pages/?alias=test
    // поиск страницы с алиасом
    public function testGetPageWithAlias()
    {
        $response = new \Bazalt\Rest\Response(200, $this->pages[3]->toArray());
        $this->assertResponse('GET /pages', ['data' => ['alias' => 'test']], $response);
    }


    // /pages?count=2
    // /pages?count=2&page=2
    public function testGetPagesLimit()
    {
        $response = new \Bazalt\Rest\Response(200, ['data' => [
            $this->pages[9]->toArray(),
            $this->pages[8]->toArray()
        ], 'pager' => [
            'current' => 1,
            'count' => 2,
            'total' => 4,
            'countPerPage' => 2
        ]]);
        $this->assertResponse('GET /pages/', ['data' => ['count' => 2]], $response);

        $response = new \Bazalt\Rest\Response(200, ['data' => [
            $this->pages[4]->toArray(),
            $this->pages[3]->toArray()
        ], 'pager' => [
            'current' => 2,
            'count' => 2,
            'total' => 4,
            'countPerPage' => 2
        ]]);
        $this->assertResponse('GET /pages/', ['data' => ['count' => 2, 'page'=> 2]], $response);
    }

    // /pages?truncate=5
    public function testGetPagesTruncate()
    {
        $page = $this->pages[9]->toArray();
        $page['body'] = ['en' => '1 2 3...', 'orig' => 'en'];
        $response = new \Bazalt\Rest\Response(200, ['data' => [
            $page
        ], 'pager' => [
            'current' => 1,
            'count' => 2,
            'total' => 2,
            'countPerPage' => 1
        ]]);
        $this->assertResponse('GET /pages/', ['data' => ['count' => 1, 'truncate' => 10, 'category_id' => $this->category2->id]], $response);
    }
}