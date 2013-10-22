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
 * @uri /auth/users/gifts
 */
class GiftsResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getList()
    {
        $collection = Gift::getCollection();

        $table = new \Bazalt\Rest\Collection($collection);
        $table->sortableBy('price');

        return new Response(Response::OK, $table->fetch($_GET));
    }
}