<?php

namespace tests\Payments\Webservice;

use Bazalt\Rest;
use Components\Payments\Model\Transaction;
use Components\Payments\Model\Account;

class TransactionTest extends \tests\BaseCase
{
    protected $transaction;

    protected $user;

    protected function setUp()
    {
        parent::setUp();

        $this->user = \Bazalt\Auth\Model\User::create();
        $this->user->login = 'test_user';
        $this->user->save();
    }

    protected function tearDown()
    {
        parent::tearDown();

        if ($this->transaction && $this->transaction->id) {
            $this->transaction->delete();
        }
        if ($this->user) {
            $this->user->delete();
        }
    }

    public function testBeginTransaction()
    {
        $account = Account::getDefault($this->user);

        $this->transaction = Transaction::beginTransaction($account, Transaction::TYPE_UP, 10, 'test');

        $transaction = Transaction::getById($this->transaction->id);

        $this->assertEquals($this->transaction->sum, $transaction->sum);
        $this->assertEquals($this->transaction->type, $transaction->type);
        $this->assertEquals($this->transaction->account_id, $account->id);
        $this->assertEquals($this->transaction->comment, $transaction->comment);
    }
}