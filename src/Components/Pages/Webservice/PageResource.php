<?php

namespace Components\Pages\Webservice;

use Bazalt\Data\Validator;
use Bazalt\Rest\Response;
use Bazalt\Site\Data\Localizable;
use Components\Pages\Model\Image;
use Components\Pages\Model\Video;
use Components\Pages\Model\Page;
use Components\Pages\Model\Tag;

/**
 * PageResource
 *
 * @uri /pages/:id
 */
class PageResource extends \Bazalt\Auth\Webservice\JWTWebservice
{
    protected function uniqueCookie($cookie, $time = null)
    {
        if (!$time) {
            $time = 60 * 60 * 24;
        }
        $isSet = isset($_COOKIE[$cookie]);
        if (!$isSet) {
            $_COOKIE[$cookie] = true;
            setcookie($cookie, true, time() + $time, '/');
        }
        return !$isSet;
    }

    /**
     * @method GET
     * @json
     */
    public function getItem($id)
    {
        $item = Page::getById($id);
        if (!$item) {
            return new Response(404, ['id' => 'Page not found']);
        }
        $user = \Bazalt\Auth::getUser();
        if ($item->status < Page::PUBLISH_STATE_PUBLISHED) {
            if ($user->isGuest() || $user->id != $item->user_id && !$user->hasPermission('pages.can_manage_other')) {
                return new Response(Response::FORBIDDEN, ['user_id' => 'This article unpublished']);
            }
        }
        return new Response(Response::OK, $item->toArray());
    }

    /**
     * @method PUT
     * @action view
     * @priority 10
     * @json
     */
    public function increaseView($id)
    {
        $item = Page::getById((int)$id);
        if (!$item) {
            return new Response(Response::NOTFOUND, '404');
        }
        if ($this->uniqueCookie('view' . $id)) {
            $item->hits++;
            $item->save();
        }
        return new Response(Response::OK, ['hits' => $item->hits]);
    }

    /**
     * @method PUT
     * @action setTop
     * @priority 10
     * @json
     */
    public function setTop($id)
    {
        $item = Page::getById((int)$id);
        if (!$item) {
            return new Response(Response::NOTFOUND, '404');
        }
        $item->is_top = !$item->is_top;
        $item->save();
        return new Response(Response::OK, $item->toArray());
    }

    /**
     * @method POST
     * @json
     */
    public function saveItem($id = null)
    {
        $user = $this->getJWTUser();
        $dataValidator = \Bazalt\Site\Data\Validator::create($this->request->data);
        $item = ($id == null) ? Page::create() : Page::getById($id);
        if (!$item) {
            return new Response(Response::NOTFOUND, ['id' => 'Page not found']);
        }
        if (!$id && !$user->hasPermission('pages.can_create')) {
            return new Response(Response::FORBIDDEN, ['id' => 'You can\'t create pages']);
        }
        if ($item->user_id != $user->id && !$user->hasPermission('pages.can_manage_other')) {
            return new Response(Response::FORBIDDEN, ['user_id' => 'You haven\'t permissions to edit foreign pages']);
        }

        $dataValidator->localizableField('title')
            ->required()
            ->length(1, 255);

        //$dataValidator->field('is_published')->bool();

        if (!$dataValidator->validate()) {
            return new Response(Response::BADREQUEST, $dataValidator->errors());
        }

        $item->title = $dataValidator['title'];
        $item->body = $dataValidator['body'];
        $item->category_id = $dataValidator['category_id'];

        if (!\Bazalt\Auth::getUser()->hasPermission('admin.access')) {
            $item->is_moderated = false;
            $item->is_allow_comments = true;
            $item->status = Page::PUBLISH_STATE_NOT_MODERATED;
            $item->template = count($dataValidator['images']) > 4 ? 'gallery.html' : 'default.html';
        } else {
            $item->status = Page::PUBLISH_STATE_PUBLISHED;
            $item->is_moderated = 1;//$dataValidator['is_published'] ? 1 : 0;
            $item->is_allow_comments = 1;//$dataValidator['is_allow_comments'] ? 1 : 0;
            $item->template = isset($dataValidator['template']) ? $dataValidator['template'] : 'default.html';
        }
        $item->is_top = $dataValidator['is_top'] ? '1' : '0';
        $item->save();

        // tags save
        $tags = [];
        Tag::decreaseQuantity($item);
        if (is_array($dataValidator['tags'])) {
            foreach ($dataValidator['tags'] as $tag) {
                $isNew = property_exists($tag, 'isNew');

                $tagObj = $isNew ? Tag::create($tag->title, $tag->url) : Tag::getById((int)$tag->id);
                if ($isNew && $tagByUrl = Tag::getByUrl($tag->url)) {
                    $tagObj = $tagByUrl;
                }
                if ($tagObj) {
                    if ($isNew) {
                        $tagObj->save();
                    }
                    $tags[$tagObj->id] = $tagObj;
                    $item->Tags->add($tagObj);
                } else {
                    throw new \Exception('Invalid tag: ' . print_r($tag, true));
                }
            }
        }
        $ids = array_keys($tags);
        Tag::increaseQuantity($ids);
        $item->Tags->clearRelations($ids);
        // end tags save

        $ids = [];
        $i = 0;
        foreach ($dataValidator['images'] as $data) {
            $image = (array)$data;
            if (isset($image['error'])) {
                continue;
            }

            $img = isset($image['id']) ? Image::getById((int)$image['id']) : Image::create();

            $img->name = isset($image['name']) ? $image['name'] : '';
            $img->title = isset($image['title']) ? $image['title'] : null;
            $img->description = isset($image['description']) ? $image['description'] : null;

            $config = \Bazalt\Config::container();
            $img->url = str_replace($config['uploads.prefix'], '', $image['url']);
            $img->size = filesize(SITE_DIR . $img->url);
            $img->is_main = isset($image['is_main']) ? 1 : 0;
            $img->sort_order = $i;

            $item->Images->add($img);
            $ids [] = $img->id;
        }

        $ids = [];
        $i = 0;
        if (isset($dataValidator['videos']) && is_array($dataValidator['videos']) && count($dataValidator['videos'])) {
            foreach ($dataValidator['videos'] as $data) {
                $video = (array)$data;
                if (empty($video['url'])) {
                    continue;
                }
                $vid = isset($video['id']) ? Video::getById((int)$video['id']) : Video::create();

                $vid->url = $video['url'];
                $vid->sort_order = $i;

                $item->Videos->add($vid);
                $ids [] = $vid->id;
            }
            $item->Videos->clearRelations($ids);
        }
        return new Response(Response::OK, $item->toArray());
    }

    /**
     * @method DELETE
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function deleteItem($id)
    {
        $item = Page::getById((int)$id);

        if (!$item) {
            return new Response(400, ['id' => "Page not found"]);
        }
        $item->delete();
        return new Response(200, true);
    }
}