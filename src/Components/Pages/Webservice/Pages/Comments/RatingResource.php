<?php

namespace Components\Pages\Webservice\Pages\Comments;

use Bazalt\Rest\Response;
use Components\Pages\Model\Comment;
use Components\Pages\Model\Page;
use Components\Pages\Model\CommentRating;

/**
 * RatingResource
 *
 * @uri /pages/:page_id/comments/:comment_id/rating
 */
class RatingResource extends \Bazalt\Rest\Resource
{
    /**
     * @method POST
     * @json
     */
    public function setRating($pageId, $commentId)
    {
        $comment = Comment::getById($commentId);
        if (!$comment || $comment->page_id != $pageId) {
            return new Response(Response::NOTFOUND, 'Comment not found');
        }

        $vote = CommentRating::getUserVote($comment);
        $vote->rating = $this->request->data->rating;
        $vote->save();

        $comment->rating = CommentRating::getRating($comment);
        $comment->save();

        return new Response(Response::OK, $comment->toArray());
    }
}