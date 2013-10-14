<?php

namespace tests\Payments\Webservice;

use Bazalt\Rest;
use Components\Payments\Model\Account;
use Components\Payments\Model\AccountType;

class AccountTest extends \tests\BaseCase
{
    protected $account;

    protected $user;

    protected function setUp()
    {
        parent::setUp();

        $this->user = \Bazalt\Auth\Model\User::create();
        $this->user->login = 'test_user';
        $this->user->save();

        $this->account = Account::getDefault($this->user);
    }

    protected function tearDown()
    {
        parent::tearDown();

        if ($this->account && $this->account->id) {
            $this->account->delete();
        }
        if ($this->user) {
            $this->user->delete();
        }
    }

    public function testGetDefaultAccount()
    {
        $accountType = AccountType::getDefaultAccountType();

        $this->assertEquals($accountType->id, $this->account->type_id);
    }

    public function testMakePayment()
    {
        $this->account->state = 10;
        $transaction = Account::makePayment($this->account, 10, 'test');

        $this->assertEquals($transaction->sum, 10);
        $this->assertEquals($this->account->state, 0);
    }

    /**
     * @expectedException \Components\Payments\Exception\NoEnoughAmount
     */
    public function testMakePaymentException()
    {
        $this->account->state = 9.9999;
        Account::makePayment($this->account, 10, 'test');
    }
}