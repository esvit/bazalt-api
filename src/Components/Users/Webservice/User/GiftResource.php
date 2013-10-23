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
 * @uri /auth/users/:user_id/gifts/:id
 */
class GiftResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getList($userId, $id)
    {
        $user = \Bazalt\Auth\Model\User::getById((int)$userId);
        $collection = Gift::getUserCollection($user);

        $table = new \Bazalt\Rest\Collection($collection);
        $table->sortableBy('price');

        return new Response(Response::OK, $table->fetch($_GET));
    }

    /**
     * @method PUT
     * @json
     */
    public function prepareGift($userId, $id)
    {
        $currentUser = \Bazalt\Auth::getUser();

        $user = \Bazalt\Auth\Model\User::getById((int)$userId);
        $gift = Gift::getById((int)$id);

        $account = \Components\Payments\Model\Account::getByUser($currentUser);
        print_r($account);

        $gift->Users->add($user, ['status' => 0]);

        return new Response(Response::OK, $gift->toArray());
    }

    /**
     * @method GET
     * @action status
     * @json
     */
    public function getStatus($userId, $id)
    {
        $currentUser = \Bazalt\Auth::getUser();

        $user = \Bazalt\Auth\Model\User::getById((int)$userId);
        $gift = Gift::getById((int)$id);

        $account = \Components\Payments\Model\Account::getDefault($currentUser);
        print_r($account);

        return new Response(Response::OK, $gift->toArray());
    }
}