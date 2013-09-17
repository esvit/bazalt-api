<?php

namespace Components\Pages\Model;

use CMS\Model\Browser;
use CMS\Tracking;

class CommentRating extends Base\CommentRating
{
    public static function create(Comment $comment)
    {
        $vote = new CommentRating();
        $vote->comment_id = $comment->id;
        if (!\Bazalt\Auth::getUser()->isGuest()) {
            $vote->user_id = \Bazalt\Auth::getUser()->id;
        }
        $vote->browser_id = Browser::getUserBrowser()->id;
        $vote->ip = Tracking::getUserIp();
        $vote->rating = 0;
        return $vote;
    }

    public static function getUserVote(Comment $comment)
    {
        $vote = \Bazalt\ORM::select('Components\\Pages\\Model\\CommentRating cr')
                    ->where('cr.comment_id = ?', $comment->id)
                    ->andWhere('cr.ip = ?', Tracking::getUserIp())
                    ->andWhere('cr.browser_id = ?', Browser::getUserBrowser()->id)
                    ->fetch();

        if (!$vote) {
            $vote = CommentRating::create($comment);
        }
        return $vote;
    }

    public static function getRating(Comment $comment)
    {
        $vote = \Bazalt\ORM::select('Components\\Pages\\Model\\CommentRating cr', 'SUM(cr.rating) AS rating')
                    ->where('cr.comment_id = ?', $comment->id)
                    ->fetch();

        return ($vote) ? $vote->rating : 0;
    }
}
