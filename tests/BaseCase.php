<?php

namespace tests;

abstract class BaseCase extends \Bazalt\Auth\Test\BaseCase
{
    protected $models = [];

    protected function setUp()
    {
        parent::setUp();

        $this->models = [];
    }

    protected function tearDown()
    {
        parent::tearDown();

        foreach ($this->models as $model) {
            $model->delete();
        }
    }

    protected function createAdminUser()
    {
        $user = \Bazalt\Auth\Model\User::create();
        $user->login = rand();
        $user->is_active = 1;
        $user->save();

        $this->models []= $user;

        $role = \Bazalt\Auth\Model\Role::create();
        $role->title = rand();
        $role->save();
        $role->addPermission('admin.access');

        $user->Roles->add($role, ['site_id' => $this->site->id]);

        $this->models []= $role;
        return $user;
    }
}