<?php

namespace tests\Pages\Webservice\Pages;

use Bazalt\Rest;
use Components\Pages\Model\Comment;
use Components\Seo\Model\Route;
use Tonic\Response;

class RouteResourceTest extends \tests\BaseCase
{
    protected $route;

    protected function setUp()
    {
        parent::setUp();

        global $loader;

        $config = array(
            'load' => array(
                $loader->findFile('Components\\Seo\\Webservice\\RouteResource'),
            )
        );
        $this->app = new \Tonic\Application($config);

        $this->route = Route::create();
        $this->route->id = 9999;
        $this->route->save();
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
        list($code, $retResponse) = $this->send('POST /pages/' . $this->page->id . '/comments', [
            'data' => json_encode([
                'nickname' => 'Test',
                'body' => 'Test body'
            ])
        ]);

        $response = new \Bazalt\Rest\Response(200, [
            'id' => $retResponse['id'],
            'nickname' => '__Test__',
            'body' => 'Test body',
            'depth' => 1,
            'rating' => 0,
            'created_at' => $retResponse['created_at']
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