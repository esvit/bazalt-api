<?php

namespace Components\Users\Model;

class Gift extends Base\Gift
{
    public static function getCollection()
    {
        $q = Gift::select();

        return new \Bazalt\ORM\Collection($q);
    }

    public static function getUserCollection(\Bazalt\Auth\Model\User $user)
    {
        $q = \Bazalt\ORM::select('Components\\Users\\Model\\Gift g')
                ->innerJoin('Components\\Users\\Model\\GiftRefUser ref', ['gift_id', 'g.id'])
                ->andWhere('ref.user_id = ?', $user->id);

        return new \Bazalt\ORM\Collection($q);
    }

    public function toArray()
    {
        $res = parent::toArray();

        $res['thumbnails'] = [
            'preview' => thumb($this->image, '96x96')
        ];
        return $res;
    }
}