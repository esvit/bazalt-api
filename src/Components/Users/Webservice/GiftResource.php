<?php

namespace Components\Users\Webservice;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Components\Users\Model\Gift;
use Bazalt\Rest\Response;

/**
 * UsersResource
 *
 * @uri /auth/users/gifts/:id
 */
class GiftResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getItem($id)
    {
        $item = Gift::getById((int)$id);

        return new Response(Response::OK, $item->toArray());
    }

    /**
     * @method POST
     * @json
     */
    public function saveItem($id)
    {
        $data = Validator::create((array)$this->request->data);

        $gift = Gift::getById($data['id']);
        if (!$gift) {
            return new Response(400, ['id' => 'Gift not found']);
        }

        //if (!$data->validate()) {
        //    return new Response(400, $data->errors());
        //}

        $gift->title = $data['title'];
        $gift->body = $data['body'];
        $gift->price = $data['price'];
        $gift->image = $data['image']->url;
        $gift->is_published = $data['is_published'] ? '1' : '0';
        $gift->save();

        return new Response(200, $gift->toArray());
    }
}