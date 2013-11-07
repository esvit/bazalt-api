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
 * @priority 10
 * @uri /auth/users/tr
 */
class GiftsTrResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getList()
    {
        $collection = Gift::getTransactions();

        $table = new \Bazalt\Rest\Collection($collection);
        $table->sortableBy('price');

        return new Response(Response::OK, $table->fetch($_GET, function($item, $gift) {
            $item['status'] = (int)$gift->status;
            $item['item_id'] = (int)$gift->item_id;
            $item['created_at'] = (int)$gift->created_at;
            $item['updated_at'] = (int)$gift->updated_at;

            $user = \Bazalt\Auth\Model\User::getById($gift->to_id);
            $item['to'] = $user->toArray();

            $user = \Bazalt\Auth\Model\User::getById($gift->user_id);
            $item['from'] = $user->toArray();
            return $item;
        }));
    }
}