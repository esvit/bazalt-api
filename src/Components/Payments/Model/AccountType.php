<?php

namespace Components\Payments\Model;

class AccountType extends Base\AccountType
{
    public static function getDefaultAccountType()
    {
        $accountType = AccountType::select()->where('site_id IS NULL')->fetch();
        if (!$accountType) {
            $accountType = new AccountType();
            $accountType->site_id = null;
            $accountType->ratio = 1;
            $accountType->title = 'Default';
            $accountType->currency = 'Bazalt Dollar';
            $accountType->save();
        }
        return $accountType;
    }
}