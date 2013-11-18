<?php

namespace Components\Pages\Webservice;
use Bazalt\Data\Validator;
use Bazalt\Rest\Response;
use Bazalt\Site\Data\Localizable;
use Components\Pages\Model\Image;
use Components\Pages\Model\Page;
use Components\Pages\Model\PageRating;
use Components\Pages\Model\Tag;

/**
 * RatingResource
 *
 * @uri /pages/:id/rating
 */
class RatingResource extends \Bazalt\Rest\Resource
{
    /**
     * @method POST
     * @json
     */
    public function setRating($pageId)
    {
        $page = Page::getById($pageId);
        if (!$page) {
            return new Response(Response::NOTFOUND, 'Page not found');
        }

        $vote = PageRating::getUserVote($page);
        $vote->rating = (int)$this->request->data->rating;
        $vote->save();

        $page->rating = PageRating::getRating($page);
        if ($page->rating >= 5) {
            $page->is_moderated = true;
        }
        $page->save();

        return new Response(Response::OK, $page->toArray());
    }
}