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

    public function toArray()
    {
        $item = parent::toArray();

        $item['status'] = (int)$this->status;

        $user = \Bazalt\Auth\Model\User::getById($this->to_id);
        $item['to'] = $user->toArray();

        $user = \Bazalt\Auth\Model\User::getById($this->user_id);
        $item['from'] = $user->toArray();

        return $item;
    }
}