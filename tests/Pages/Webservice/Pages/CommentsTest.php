<?php

namespace tests\Pages\Webservice\Pages;

use Bazalt\Rest;
use Components\Pages\Model\Comment;
use Components\Pages\Model\Page;
use Tonic\Response;

class CommentsTest extends \tests\BaseCase
{
    protected $app;

    protected $page;

    protected function setUp()
    {
        global $loader;

        $config = array(
            'load' => array(
                $loader->findFile('Components\\Pages\\Webservice\\Pages\\CommentsResource'),
            )
        );
        $this->app = new \Tonic\Application($config);

        $this->page = Page::create();
        $this->page->id = 9999;
        $this->page->save();
    }

    protected function tearDown()
    {
        $this->page->delete();
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
                'nickname' => 'Test',
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
            'nickname' => 'Test',
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