<?php

namespace Components\Payments\Model;

class Account extends Base\Account
{
    public static function getByUser(\Bazalt\Auth\Model\User $user)
    {
        $types = AccountType::select()->fetchAll();

        $accounts = Account::select()
                           ->where('user_id = ?', $user->id)->fetchAll();
        if (count($accounts) < count($types)) {
            foreach ($types as $type) {
                $exists = false;
                foreach ($accounts as $account) {
                    if ($account->type == $type->id) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $account = new Account();
                    $account->type = $type->id;
                    $account->user_id = $user->id;
                    $account->state = 0;
                    $account->save();
                }
            }
            $accounts = Account::select()
                               ->where('user_id = ?', $user->id)->fetchAll();
        }
        return $accounts;
    }

    public static function getDefault(\Bazalt\Auth\Model\User $user)
    {
        $type = AccountType::getDefaultAccountType();
        if (!$type) {
            self::getByUser($user);
        }
        $q = Account::select()
            ->where('user_id = ?', $user->id)
            ->andWhere('type_id = ?', $type->id)
            ->limit(1);
        $account = $q->fetch();

        if (!$account) {
            $account = new Account();
            $account->user_id = $user->id;
            $account->type_id = $type->id;
            $account->state = 0;
            $account->save();
        }
        return $account;
    }

    public static function makePayment(Account $account, $sum, $data)
    {
        $transaction = Transaction::beginTransaction($account, Transaction::TYPE_DOWN, $sum);
        if ($sum > $account->state) {
            $msg = 'There are the not enough amounts of balance';//, ComPay::getName());
            $transaction->failed(['message' => $msg]);
            throw new \Components\Payments\Exception\NoEnoughAmount($msg);
        }
        $account->state -= (int)$sum;
        $transaction->complete($data);
        return $transaction;
    }

    public static function userBalancePlus(Account $account, $sum, $data, $comment)
    {
        $transaction = Transaction::beginTransaction($account, Transaction::TYPE_UP, $sum, $comment);
        $transaction->complete($data, $comment);
    }

    public static function userBalanceMinus(Account $account, $sum, $data, $comment)
    {
        $transaction = Transaction::beginTransaction($account, Transaction::TYPE_DOWN, $sum, $comment);
        $transaction->complete($data, $comment);
    }

    public static function getCollection()
    {
        $q = Account::select()
                    ->groupBy('user_id');

        return new \Bazalt\ORM\Collection($q);
    }

    public static function getUserAccountsTypes($ids)
    {
        $q = Account::select()
                    ->where('user_id = ?', $ids);

        return $q->fetchAll();
    }
}