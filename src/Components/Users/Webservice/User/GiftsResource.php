<?php

namespace Components\Users\Webservice\User;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Components\Users\Model\Gift;
use Bazalt\Rest\Response;

/**
 * UsersResource
 *
 * @uri /auth/users/:user_id/gifts
 */
class GiftsResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getList($id)
    {
        $user = \Bazalt\Auth\Model\User::getById((int)$id);
        $collection = Gift::getUserCollection($user);

        $table = new \Bazalt\Rest\Collection($collection);
        $table->sortableBy('price');

        return new Response(Response::OK, $table->fetch($_GET, function($item, $gift) {
            $item['status'] = (int)$gift->status;

            $user = \Bazalt\Auth\Model\User::getById($gift->to_id);
            $item['to'] = $user->toArray();
            return $item;
        }));
    }
}