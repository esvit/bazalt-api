<?php

namespace tests;

use Tonic;
use Bazalt\Rest;

abstract class BaseCase extends \Bazalt\Rest\Test\BaseCase
{
    protected $site = null;

    protected $user = null;

    protected function setUp()
    {
        $this->site = \Bazalt\Site\Model\Site::create();
        $this->site->save();

        $user = \Bazalt\Auth\Model\User::getUserByLogin('Test');
        if ($user) {
            $user->delete();
        }

        $this->user = \Bazalt\Auth\Model\User::create();
        $this->user->login = 'Test';
        $this->user->is_active = 1;
        $this->user->save();

        \Bazalt\Auth::setUser($this->user);

        \Bazalt\Site::setCurrent($this->site);
    }

    protected function tearDown()
    {
        if ($this->site->id) {
            $this->site->delete();
        }
        if ($this->user->id) {
            $this->user->delete();
        }
        $this->site = null;
        $this->user = null;
        \Bazalt\Site::setCurrent(false);
        \Bazalt\Auth::setUser(false);
    }
}