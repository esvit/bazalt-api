<?php

namespace Components\Users\Webservice;
use Bazalt\Data\Validator;
use Tonic\Response;

/**
 * RolesResource
 *
 * @uri /auth/roles
 */
class RolesResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getList()
    {
        $roles = \Bazalt\Auth\Model\Role::getAll();
        $res = [];
        foreach($roles as $role) {
            $res []= [
                'id' => $role->id,
                'title' => $role->title
            ];
        }
        return new Response(Response::OK, array('data' => $res));
    }

    /**
     * @method GET
     * @action permissions
     * @json
     */
    public function getPermissions()
    {
        $curUser = \Bazalt\Auth::getUser();
        if (!$curUser->hasPermission('auth.can_manage_roles')) {
            return new Response(Response::FORBIDDEN, 'Permission denied');
        }

        $permissions = \Bazalt\Auth\Model\Permission::getAll();
        $res = [];
        foreach($permissions as $permission) {
            $res []= [
                'id' => $permission->id,
                'title' => $permission->description
            ];
        }
        return new Response(Response::OK, array('data' => $res));
    }

    /**
     * @method POST
     * @json
     */
    public function saveRole()
    {
        $data = Validator::create((array)$this->request->data);
        $data->field('title')->required();
        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        $curUser = \Bazalt\Auth::getUser();
        if (!$curUser->hasPermission('auth.can_manage_roles')) {
            return new Response(Response::FORBIDDEN, 'Permission denied');
        }

        $role = \Bazalt\Auth\Model\Role::create();
        $role->title = $data['title'];
        $role->description = $data['description'];
        $role->save();

        $ids = [];
        foreach ($data['permissions'] as $permission) {
            $perm = \Bazalt\Auth\Model\Permission::getById($permission);

            $role->Permissions->add($perm);
            $ids [] = $perm->id;
        }
        $role->Permissions->clearRelations($ids);
        $res = $role->toArray();
        $res['permissions'] = [];
        $permissions = $role->getPermissions();
        foreach($permissions as $permission) {
            $res['permissions'] []= $permission->id;
        }

        return new Response(Response::OK, $res);
    }
}
