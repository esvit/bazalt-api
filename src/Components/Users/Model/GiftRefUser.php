<?php

namespace Components\Users\Model;

class GiftRefUser extends Base\GiftRefUser
{
    public static function getCollection()
    {
        $q = GiftRefUser::select();

        return new \Bazalt\ORM\Collection($q);
    }
}