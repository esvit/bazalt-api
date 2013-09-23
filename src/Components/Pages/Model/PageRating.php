<?php

namespace Components\Pages\Model;

use CMS\Model\Browser;
use CMS\Tracking;

class PageRating extends Base\PageRating
{
    public static function create(Page $page)
    {
        $vote = new PageRating();
        $vote->page_id = $page->id;
        if (!\Bazalt\Auth::getUser()->isGuest()) {
            $vote->user_id = \Bazalt\Auth::getUser()->id;
        }
        $vote->browser_id = Browser::getUserBrowser()->id;
        $vote->ip = Tracking::getUserIp();
        $vote->rating = 0;
        return $vote;
    }

    public static function getUserVote(Page $page)
    {
        $vote = \Bazalt\ORM::select('Components\\Pages\\Model\\PageRating cr')
                    ->where('cr.page_id = ?', $page->id)
                    ->andWhere('cr.ip = ?', Tracking::getUserIp())
                    ->andWhere('cr.browser_id = ?', Browser::getUserBrowser()->id)
                    ->fetch();

        if (!$vote) {
            $vote = PageRating::create($page);
        }
        return $vote;
    }

    public static function getRating(Page $page)
    {
        $vote = \Bazalt\ORM::select('Components\\Pages\\Model\\PageRating cr', 'SUM(cr.rating) AS rating')
                    ->where('cr.page_id = ?', $page->id)
                    ->fetch();

        return ($vote) ? $vote->rating : 0;
    }
}
