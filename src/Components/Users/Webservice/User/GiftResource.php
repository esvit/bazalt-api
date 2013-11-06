<?php

namespace Components\Users\Webservice\User;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Components\Users\Model\Gift;
use Components\Payments\Model\Account;
use Components\Payments\Model\Transaction;
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
        if ($currentUser->isGuest()) {
            return new Response(Response::FORBIDDEN, ['user' => 'Permission denied']);
        }

        $user = \Bazalt\Auth\Model\User::getById((int)$userId);
        if (!$user) {
            return new Response(Response::FORBIDDEN, ['user_id' => 'User not found']);
        }
        $gift = Gift::getById((int)$id);
        if (!$gift) {
            return new Response(Response::FORBIDDEN, ['id' => 'Gift not found']);
        }

        $account = Account::getDefault(\Bazalt\Auth::getUser());
        if ($account->state >= $gift->price) {
            $tr = Transaction::beginTransaction($account, Transaction::TYPE_DOWN, (int)$gift->price);
            $tr->complete('For gift #' . $gift->id);
        }

        $gift->Users->add(\Bazalt\Auth::getUser(), ['status' => 0, 'to_id' => $user->id]);

        return $this->getStatus($userId, $id);
    }

    /**
     * @method GET
     * @action status
     * @json
     */
    public function getStatus($userId, $id)
    {
        $currentUser = \Bazalt\Auth::getUser();
        if ($currentUser->isGuest()) {
            return new Response(Response::FORBIDDEN, ['user' => 'Permission denied']);
        }

        $user = \Bazalt\Auth\Model\User::getById((int)$userId);
        if (!$user) {
            return new Response(Response::FORBIDDEN, ['user_id' => 'User not found']);
        }
        $gift = Gift::getById((int)$id);
        if (!$gift) {
            return new Response(Response::FORBIDDEN, ['id' => 'Gift not found']);
        }

        $account = \Components\Payments\Model\Account::getDefault($currentUser);
        if ($account->state < $gift->price) {
            return new Response(Response::PAYMENTREQUIRED, ['price' => $gift->price, 'diff' => $gift->price - $account->state]);
        }
        print_r($account);

        return new Response(Response::OK, $gift->toArray());
    }
}