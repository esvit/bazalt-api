<?php

namespace tests\Pages\Webservice\Pages;

use Bazalt\Rest;
use Components\Pages\Model\Comment;
use Components\Pages\Model\Page;
use Tonic\Response;

class PageTest extends \tests\BaseCase
{
    protected $app;

    protected $page;

    protected function setUp()
    {
        parent::setUp();

        global $loader;

        $config = array(
            'load' => array(
                $loader->findFile('Components\\Pages\\Webservice\\PageResource'),
                $loader->findFile('Components\\Pages\\Webservice\\PagesResource'),
            )
        );
        $this->app = new \Tonic\Application($config);

        $this->page = Page::create();
        $this->page->id = 9999;
        $this->page->save();
    }

    protected function tearDown()
    {
        parent::tearDown();

        if ($this->page && $this->page->id) {
            $this->page->delete();
        }
    }

    public function testGetNotFound()
    {
        // not found
        $response = new \Bazalt\Rest\Response(404, ['id' => 'Page not found']);
        $this->assertResponse('GET /pages/' . 99999, [], $response);
    }

    public function testGetUnpublishedPageByAuthor()
    {
        $response = new \Bazalt\Rest\Response(200, [
            'id' => 9999,
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'category_id' => null,
            'template' => 'default.html',
            'is_published' => 0,
            'is_allow_comments' => 1,
            'hits' => 0,
            'comments_count' => 0,
            'rating' => 0,
            'title' => '',
            'body' => '',
            'created_at' => strToTime($this->page->created_at) * 1000,
            'updated_at' => strToTime($this->page->updated_at) * 1000,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->getName()
            ],
            'tags' => [],
            'images' => []
        ]);
        $this->assertResponse('GET /pages/' . $this->page->id, [], $response);
    }

    public function testGetUnpublishedPageByOtherUser()
    {
        $user = \Bazalt\Auth\Model\User::create();
        $user->login = 'Vasya';
        $user->is_active = 1;
        $this->models [] = $user;
        $user->save();
        \Bazalt\Auth::setUser($user);

        $response = new \Bazalt\Rest\Response(Response::FORBIDDEN, ['user_id' => 'This article unpublished']);
        $this->assertResponse('GET /pages/' . $this->page->id, [], $response);
    }

    public function testIncreaseViews()
    {
        $_COOKIE = [];
        $response = new \Bazalt\Rest\Response(Response::OK, ['hits' => 1]);
        $this->assertResponse('PUT /pages/' . $this->page->id, ['_GET' => ['action' => 'view']], $response);

        $response = new \Bazalt\Rest\Response(Response::OK, ['hits' => 1]);
        $this->assertResponse('PUT /pages/' . $this->page->id, ['_GET' => ['action' => 'view']], $response);

        unset($_COOKIE['view' . $this->page->id]);

        $response = new \Bazalt\Rest\Response(Response::OK, ['hits' => 2]);
        $this->assertResponse('PUT /pages/' . $this->page->id, ['_GET' => ['action' => 'view']], $response);
    }

    public function testGetUnpublishedPageByGod()
    {
        $user = \Bazalt\Auth\Model\User::create();
        $user->login = 'Vasya';
        $user->is_active = 1;
        $user->is_god = 1;
        $this->models [] = $user;
        $user->save();
        \Bazalt\Auth::setUser($user);

        $response = new \Bazalt\Rest\Response(200, [
            'id' => 9999,
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'category_id' => '',
            'template' => 'default.html',
            'is_published' => 0,
            'is_allow_comments' => 1,
            'hits' => 0,
            'comments_count' => 0,
            'rating' => 0,
            'title' => '',
            'body' => '',
            'created_at' => strToTime($this->page->created_at) * 1000,
            'updated_at' => strToTime($this->page->updated_at) * 1000,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->getName()
            ],
            'tags' => [],
            'images' => []
        ]);
        $this->assertResponse('GET /pages/' . $this->page->id, [], $response);
    }

    public function testPostForbidenForUser()
    {
        $response = new \Bazalt\Rest\Response(Response::FORBIDDEN, ['id' => 'You can\'t create pages']);
        $this->assertResponse('POST /pages', [], $response);
    }

    public function testPostNotFound()
    {
        $response = new \Bazalt\Rest\Response(Response::NOTFOUND, ['id' => 'Page not found']);
        $this->assertResponse('POST /pages/99999', [], $response);
    }

    public function testPostValidation()
    {
        $response = new \Bazalt\Rest\Response(Response::BADREQUEST, [
            'title' => [
                'nested' => [
                    'en' => [
                        'required' => 'Field cannot be empty',
                        'length' => [
                            'minlength' => 'String must be more then 1 symbols'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertResponse('POST /pages/' . $this->page->id, [], $response);
    }

    public function testPostByGod()
    {
        $user = $this->createAdminUser();
        \Bazalt\Auth::setUser($user);

        list($code, $retResponse) = $this->send('POST /pages/', [
            'data' => json_encode([
                'title' => ['en' => 'Test'],
                'body' => ['en' => 'Body']
            ])
        ]);

        $response = new \Bazalt\Rest\Response(200, [
            'id' => $retResponse['id'],
            'site_id' => $this->site->id,
            'user_id' => $user->id,
            'category_id' => null,
            'template' => 'default.html',
            'is_published' => 1,
            'is_allow_comments' => 1,
            'hits' => null,
            'comments_count' => null,
            'rating' => 0,
            'title' => [
                'en' => 'Test',
                'orig' => 'en'
            ],
            'body' => [
                'en' => 'Body',
                'orig' => 'en'
            ],
            'created_at' => $retResponse['created_at'],
            'updated_at' => $retResponse['updated_at'],
            'user' => [
                'id' => $user->id,
                'name' => $user->getName()
            ],
            'tags' => [],
            'images' => []
        ]);

        $this->assertEquals($response->code, $code, json_encode($retResponse));
        $this->assertEquals($response->body, $retResponse);
    }

    public function testPostByUser()
    {
        $role = \Bazalt\Auth\Model\Role::create();
        $role->title = rand();
        $role->save();
        $role->addPermission('pages.can_create');

        $this->user->Roles->add($role, ['site_id' => $this->site->id]);

        $this->models []= $role;

        list($code, $retResponse) = $this->send('POST /pages/', [
            'data' => json_encode([
                'title' => ['en' => 'Test'],
                'body' => ['en' => 'Body']
            ])
        ]);

        $response = new \Bazalt\Rest\Response(200, [
            'id' => $retResponse['id'],
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'category_id' => null,
            'template' => 'default.html',
            'is_published' => 1,
            'is_allow_comments' => 1,
            'hits' => null,
            'comments_count' => null,
            'rating' => 0,
            'title' => [
                'en' => 'Test',
                'orig' => 'en'
            ],
            'body' => [
                'en' => 'Body',
                'orig' => 'en'
            ],
            'created_at' => $retResponse['created_at'],
            'updated_at' => $retResponse['updated_at'],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->getName()
            ],
            'tags' => [],
            'images' => []
        ]);

        $this->assertEquals($response->code, $code, json_encode($retResponse));
        $this->assertEquals($response->body, $retResponse);
    }
}