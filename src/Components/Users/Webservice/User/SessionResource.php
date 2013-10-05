<?php

namespace Components\Users\Webservice\User;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Bazalt\Rest\Response;

/**
 * SessionResource
 *
 * @uri /auth/session
 */
class SessionResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getUser()
    {
        $user = \Bazalt\Auth::getUser();
        return new Response(Response::OK, $user->toArray());
    }

    /**
     * @method POST
     * @json
     */
    public function login()
    {
        $user = null;
        $data = Validator::create($this->request->data);
        $data->field('password')->required();
        $data->field('email')->required()->validator('exist_user', function($value) use (&$user, $data) {
            $user = User::getUserByLoginPassword($value, $data['password'], true);
            return ($user != null);
        }, 'User with this email does not exists');

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }
        $user->login($data['remember_me'] == 'true');
        return new Response(Response::OK, $user->toArray());
    }

    /**
     * @method DELETE
     * @json
     */
    public function logout()
    {
        $user = \Bazalt\Auth::getUser();
        if ($user->isGuest()) {
            return new Response(Response::OK, false);
        }
        \Bazalt\Auth::logout();
        return new Response(Response::OK, true);
    }
}