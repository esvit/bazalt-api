<?php

namespace tests\Payments\Webservice;

use Bazalt\Rest;
use Components\Payments\Model\AccountType;

class AccountTypeTest extends \tests\BaseCase
{
    protected $accountType;

    protected function tearDown()
    {
        parent::tearDown();

        if ($this->accountType && $this->accountType->id) {
            $this->accountType->delete();
        }
    }

    public function testGetDefaultAccountType()
    {
        $this->accountType = AccountType::getDefaultAccountType();

        $this->assertNotNull($this->accountType->id);
        $this->assertNull($this->accountType->site_id);

        $accountType = AccountType::getDefaultAccountType();

        $this->assertEquals($this->accountType->id, $accountType->id);
    }
}