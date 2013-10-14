<?php

namespace tests;

use Tonic;
use Bazalt\Rest;

abstract class BaseCase extends \Bazalt\Rest\Test\BaseCase
{
    protected $site = null;

    protected function setUp()
    {
        $this->site = \Bazalt\Site\Model\Site::create();
        $this->site->save();

        \Bazalt\Site::setCurrent($this->site);
    }

    protected function tearDown()
    {
        if ($this->site->id) {
            $this->site->delete();
        }
        $this->site = null;
        \Bazalt\Site::setCurrent(false);
    }
}