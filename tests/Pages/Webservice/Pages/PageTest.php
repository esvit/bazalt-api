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

    public function testGet()
    {
        // empty comments
        $response = new \Bazalt\Rest\Response(200, []);
        $this->assertResponse('GET /pages/' . $this->page->id . '/comments', [], $response);

        $rootComment = Comment::getRoot($this->page);

        $comment = Comment::create($this->page);
        $comment->id = 9999;
        $comment->nickname = 'Test';
        $comment->body = 'Test body';
        $rootComment->Elements->add($comment);

        // 1 comment
        $response = new \Bazalt\Rest\Response(200, [
            [
                'id' => 9999,
                'nickname' => '__Test__',
                'body' => 'Test body',
                'depth' => 1,
                'rating' => 0,
                'created_at' => strtotime($comment->created_at) . '000'
            ]
        ]);
        $this->assertResponse('GET /pages/' . $this->page->id . '/comments', [], $response);
    }


    public function testPost()
    {
        $user = $this->createAdminUser();
        \Bazalt\Auth::setUser($user);

        list($code, $retResponse) = $this->send('POST /pages/', [
            'data' => json_encode([
                'title' => ['en' => 'Test'],
                'body' => ['en' => 'Body'],
                'is_published' => 'true'
            ])
        ]);

        $response = new \Bazalt\Rest\Response(200, [
            'id' => $retResponse['id'],
            'site_id' => $this->site->id,
            'user_id' => $user->id,
            'category_id' => null,
            'url' => '/post-' . $retResponse['id'],
            'template' => 'default.html',
            'is_published' => 1,
            'is_allow_comments' => null,
            'hits' => null,
            'comments_count' => null,
            'rating' => 0,
            'lang_id' => null,
            'completed' => 0,
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

        $this->assertEquals($response->body, $retResponse);
        $this->assertEquals($response->code, $code);
    }

    public function testPostWithEmptyNickname()
    {
        $response = new \Bazalt\Rest\Response(Response::BADREQUEST, [
            'nickname' => [
                'required' => 'Field cannot be empty'
            ]
        ]);
        $this->assertResponse('POST /pages/' . $this->page->id . '/comments', [
            'data' => json_encode([
                'body' => 'Test body'
            ])
        ], $response);
    }
}