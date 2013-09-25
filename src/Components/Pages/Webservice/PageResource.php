<?php

namespace Components\Pages\Webservice;
use Bazalt\Data\Validator;
use Bazalt\Rest\Response;
use Bazalt\Site\Data\Localizable;
use Components\Pages\Model\Image;
use Components\Pages\Model\Page;
use Components\Pages\Model\Tag;

/**
 * PageResource
 *
 * @uri /pages/:id
 */
class PageResource extends \Bazalt\Rest\Resource
{
    protected function uniqueCookie($cookie, $time = null)
    {
        if (!$time) {
            $time = 60 * 60 * 24;
        }
        $isSet = isset($_COOKIE[$cookie]);
        if (!$isSet) {
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
            return new Response(404, ['id' => 'Article not found']);
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
        return new Response(Response::OK, '' . $item->hits);
    }

    /**
     * @method POST
     * @json
     */
    public function saveItem($id = null)
    {
        $dataValidator = \Bazalt\Site\Data\Validator::create($this->request->data);
        $item = ($id == null) ? Page::create() : Page::getById($id);
        if (!$item) {
            return new Response(Response::NOTFOUND, '404');
        }

        $dataValidator->localizableField('title')
            ->required()
            ->length(5, 255);

        //$dataValidator->field('is_published')->bool();

        if (!$dataValidator->validate()) {
            return new Response(400, $dataValidator->errors());
        }

        $item->title = $dataValidator['title'];
        $item->body = $dataValidator['body'];

        if (!\Bazalt\Auth::getUser()->hasPermission('admin.access')) {
            $item->is_published = true;
            $item->is_allow_comments = true;
            $item->category_id = 6;
            $item->template = count($dataValidator['images']) > 4 ? 'gallery.html' : 'default.html';
        } else {
            $item->is_published = $dataValidator['is_published'] ? 1 : 0;
            $item->is_allow_comments = $dataValidator['is_allow_comments'] ? 1 : 0;
            $item->category_id = $dataValidator['category_id'];
            $item->template = isset($dataValidator['template']) ? $dataValidator['template'] : 'default.html';
        }
        $item->is_top = $dataValidator['is_top'];
        $item->save();

        // tags save
        $tags = [];
        Tag::decreaseQuantity($item);
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

            $img->name = $image['name'];
            $img->title = isset($image['title']) ? $image['title'] : null;
            $img->description = isset($image['description']) ? $image['description'] : null;
            $img->url = $image['url'];
            $img->size = $image['size'];
            $img->sort_order = $i;

            $item->Images->add($img);
            $ids [] = $img->id;
        }
        $item->Images->clearRelations($ids);
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