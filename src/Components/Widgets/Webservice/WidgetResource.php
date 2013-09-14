<?php

namespace Components\Widgets\Webservice;
use Bazalt\Rest\Response;
use Components\Widgets\Model\Widget;

/**
 * WidgetResource
 *
 * @uri /widgets/:id
 */
class WidgetResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getItem($id)
    {
        $item = Widget::getById($id);
        if (!$item) {
            return new Response(400, ['id' => 'Widget not found']);
        }
        return new Response(Response::OK, $item->toArray());
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

        $dataValidator->field('is_published')->bool();

        if (!$dataValidator->validate()) {
            return new Response(400, $dataValidator->errors());
        }

        $item->title = $dataValidator['title'];
        $item->body = $dataValidator['body'];
        $item->is_published = $dataValidator['is_published'] ? 1 : 0;
        $item->is_top = $dataValidator['is_top'];

        $item->save();

        return new Response(Response::OK, $item->toArray());
    }
}