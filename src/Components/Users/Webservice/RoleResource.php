<?php

namespace Components\Users\Webservice;
use Bazalt\Data\Validator;
use Tonic\Response;

/**
 * RoleResource
 *
 * @uri /auth/roles/:id
 */
class RoleResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getRole($id)
    {
        $role = \Bazalt\Auth\Model\Role::getById($id);
        if (!$role) {
            return new Response(400, ['id' => 'Role not found']);
        }
        $res = $role->toArray();
        $res['permissions'] = [];
        $permissions = $role->getPermissions();
        foreach($permissions as $permission) {
            $res['permissions'] []= $permission->id;
        }

        return new Response(Response::OK, $res);
    }

    /**
     * @method POST
     * @method PUT
     * @json
     */
    public function saveRole($id)
    {
        $data = Validator::create((array)$this->request->data);
        $data->field('id')->required();
        $data->field('title')->required();
        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        $role = \Bazalt\Auth\Model\Role::getById($id);
        if (!$role) {
            return new Response(400, ['id' => 'Role not found']);
        }

        $curUser = \Bazalt\Auth::getUser();
        if (!$curUser->hasPermission('auth.can_manage_roles')) {
            return new Response(Response::FORBIDDEN, 'Permission denied');
        }

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
