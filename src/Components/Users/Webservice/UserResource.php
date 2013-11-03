<?php

namespace Components\Users\Webservice;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Components\Users\Model\Image;
use Bazalt\Data\Validator;
use Tonic\Response;

/**
 * UserResource
 *
 * @uri /auth/users/:id
 */
class UserResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getUser($id)
    {
        $user = User::getById($id);
        if (!$user) {
            return new Response(400, ['id' => 'User not found']);
        }
        $res = $user->toArray();
        $res['profile'] = unserialize($user->setting('registrationData'));
        $res['images'] = [];
        $images = Image::getUserImages($user->id);
        foreach ($images as $image ) {
            $res['images'] []= $image->toArray();
        }
        return new Response(Response::OK, $res);
    }

    /**
     * @method PUT
     * @action changePassword
     * @json
     */
    public function changePassword($id)
    {
        $user = User::getById($id);
        if (!$user || $user->is_deleted || !$user->is_active) {
            return new Response(400, ['id' => 'User not found']);
        }
        $current = \Bazalt\Auth::getUser();
        if ($user->id != $current->id) {
            return new Response(403, 'Permission denied');
        }
        $data = (array)$this->request->data;
        if (!isset($data['old_password']) || User::cryptPassword($data['old_password']) != $user->password) {
            return new Response(Response::BADREQUEST, [
                'old_password' => ['invalid' => 'Invalid old password']
            ]);
        }
        if (!isset($data['new_password'])) {
            return new Response(Response::BADREQUEST, [
                'new_password' => ['invalid' => 'Invalid new password']
            ]);
        }
        $user->password = User::cryptPassword($data['new_password']);
        $user->save();
        return new Response(Response::OK, $user->toArray());
    }

    /**
     * @method PUT
     * @action activate
     * @json
     */
    public function activateUser($id)
    {
        $user = User::getById($id);
        if (!$user || $user->is_deleted) {
            return new Response(400, ['id' => 'User not found']);
        }
        if (!isset($_GET['key']) || $user->getActivationKey() != trim($_GET['key'])) {
            return new Response(Response::BADREQUEST, [
                'key' => ['invalid' => 'Invalid activation key']
            ]);
        }
        if ($user->is_active) {
            return new Response(Response::BADREQUEST, [
                'key' => ['user_activated' => 'User already activated']
            ]);
        }
        $user->is_active = 1;
        $user->save();
        return new Response(Response::OK, $user->toArray());
    }

    /**
     * @method DELETE
     * @json
     */
    public function deleteUser($id)
    {
        $user = \Bazalt\Auth::getUser();
        $profile = User::getById($id);
        if (!$profile) {
            return new Response(400, ['id' => 'User not found']);
        }
        if (!$user->hasPermission('auth.can_delete_user')) {
            return new Response(Response::FORBIDDEN, 'Permission denied');
        }
        if (!$user->isGuest() && $user->id == $profile->id) {
            return new Response(Response::BADREQUEST, ['id' => 'Can\'t delete yourself']);
        }
        $profile->is_deleted = 1;
        $profile->save();
        return new Response(Response::OK, true);
    }

    /**
     * @method PUT
     * @method POST
     * @json
     */
    public function saveUser()
    {
        $data = Validator::create((array)$this->request->data);

        $emailField = $data->field('email')->required()->email();

        $user = User::getById($data['id']);
        if (!$user) {
            return new Response(400, ['id' => 'User not found']);
        }

        $userRoles = [];
        $data->field('roles')->validator('validRoles', function($roles) use (&$userRoles) {
            if ($roles) {
                foreach ($roles as $role) {
                    $userRoles[$role] = Role::getById($role);
                    if (!$userRoles[$role]) {
                        return false;
                    }
                }
            }
            return true;
        }, 'Invalid roles');

        $data->field('login')->required();
        $data->field('gender')->required();

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        $user->login = $data['login'];
        $user->email = $data['email'];
        $user->firstname = $data['firstname'];
        $user->secondname = $data['secondname'];
        $user->patronymic = $data['patronymic'];
        $user->birth_date = date('Y-m-d', strToTime($data['birth_date']));
        //$user->password = User::cryptPassword($data['password']);
        $user->gender = $data['gender'];
        $user->is_active = $data['is_active'];
        $user->is_deleted = $data['is_deleted'];
        $user->save();

        //$user->Roles->clearRelations(array_keys($userRoles));
        foreach ($userRoles as $role) {
        //    $user->Roles->add($role, ['site_id' => 6]);
        }

        $ids = [];
        $i = 0;
        $dataV = $data;
        if (isset($dataV['images']) && count($dataV['images'])) {
            foreach ($dataV['images'] as $data) {
                $image = (array)$data;
                if (isset($image['error'])) {
                    continue;
                }

                $img = isset($image['id']) ? Image::getById((int)$image['id']) : Image::create();

                $img->name = $image['name'];
                $img->title = isset($image['title']) ? $image['title'] : null;
                $img->description = isset($image['description']) ? $image['description'] : null;

                $config = \Bazalt\Config::container();
                $img->url = str_replace($config['uploads.prefix'], '', $image['url']);
                $img->size = $image['size'];
                $img->sort_order = $i;
                $img->is_main = $image['is_main'] == 'true';
                $img->user_id = $user->id;
                $img->save();

                $ids [] = $img->id;
            }
        }
        return new Response(200, $user->toArray());
    }
}