<?php

namespace Components\Users\Webservice\User;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Components\Users\Model\GiftRefUser;
use Bazalt\Rest\Response;

/**
 * UsersResource
 *
 * @priority 10
 * @uri /auth/users/tr/:id
 */
class GiftTrResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getItem($id)
    {
        $item = GiftRefUser::getById($id);
        if (!$item) {
            return new Response(404, ['id' => 'Article not found']);
        }
        return new Response(Response::OK, $item->toArray());
    }
}