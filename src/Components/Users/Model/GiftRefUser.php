<?php

namespace Components\Users\Model;

class GiftRefUser extends Base\GiftRefUser
{
    public static function getById($id)
    {
        $q = GiftRefUser::select()
                ->where('item_id = ?', (int)$id);

        return $q->fetch();
    }

    public static function getCollection()
    {
        $q = GiftRefUser::select();

        return new \Bazalt\ORM\Collection($q);
    }
}