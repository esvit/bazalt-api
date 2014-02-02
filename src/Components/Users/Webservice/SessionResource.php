<?php

namespace Components\Users\Webservice;
use Bazalt\Auth\Model\User;
use Bazalt\Config;
use Bazalt\Data\Validator;
use Bazalt\Rest\Response;
use Components\Payments\Model\Account;

/**
 * SessionResource
 *
 * @uri /auth/session
 */
class SessionResource extends \Bazalt\Auth\Webservice\JWTWebservice
{
    /**
     * @method GET
     * @json
     */
    public function getUser()
    {
        $user = $this->getJWTUser();

        $res = $user->toArray();
        /*if (!$user->isGuest()) {
            $account = Account::getDefault($user);
            $res['account'] = $account->state;
        }*/

        return new Response(Response::OK, $res);
    }

    /**
     * @method PUT
     * @json
     */
    public function renewSession()
    {
        return $this->getUser();
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

        $res = $user->toArray();

        if (!$user->isGuest()) {
            $account = Account::getDefault($user);
            $res['account'] = $account->state;
        }

        $res['jwt_token'] = $this->getJWTToken($user);

        return new Response(Response::OK, $res);
    }

    /**
     * @method DELETE
     * @json
     */
    public function logout()
    {
        if (!\Bazalt\Auth::getUser()->isGuest()) {
            \Bazalt\Auth::logout();
        }
        return $this->getUser();
    }
}