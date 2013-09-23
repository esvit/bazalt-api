<?php

namespace Components\Pages\Webservice\Pages;
use Bazalt\Data\Validator;
use Bazalt\Rest\Response;
use Components\Pages\Model\Category;
use Components\Pages\Model\Comment;
use Components\Pages\Model\Page;

/**
 * CommentsResource
 *
 * @uri /pages/:page_id/comments
 */
class CommentsResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getItems($pageId)
    {
        $page = Page::getById((int)$pageId);
        if (!$page) {
            return new Response(Response::NOTFOUND, 'Page with id "' . $pageId . '" not found');
        }

        $data = [];
        $comments = Comment::getCommentsForItem($page);
        $comments = \Bazalt\ORM\Relation\NestedSet::makeTree($comments);
        foreach ($comments as $comment) {
            $data []= $comment->toArray();
        }
        return new Response(Response::OK, $data);
    }

    /**
     * @method POST
     * @json
     */
    public function newComment($pageId)
    {
        $page = Page::getById((int)$pageId);
        if (!$page) {
            return new Response(Response::NOTFOUND, 'Page with id "' . $pageId . '" not found');
        }

        $data = Validator::create($this->request->data);

        $data->field('nickname')->required();
        $data->field('body')->required();

        if (!$data->validate()) {
            return new Response(Response::BADREQUEST, $data->errors());
        }

        $comment = Comment::create($page);
        $comment->nickname = $data['nickname'];
        $comment->body = $data['body'];

        if (isset($data['reply_to'])) {
            $rootComment = Comment::getById($data['reply_to']);
            if (!$rootComment) {
                return new Response(Response::NOTFOUND, 'Comment for reply with id "' . $data['reply_to'] . '" not found');
            }
        } else {
            $rootComment = Comment::getRoot($page);
        }
        $rootComment->Elements->add($comment);

        $page->comments_count++;
        $page->save();

        return new Response(Response::OK, $comment->toArray());
    }
}