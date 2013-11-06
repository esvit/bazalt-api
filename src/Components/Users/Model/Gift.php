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

    public static function getTransactions()
    {
        $q = \Bazalt\ORM::select('Components\\Users\\Model\\Gift g')
                ->innerJoin('Components\\Users\\Model\\GiftRefUser ref', ['gift_id', 'g.id']);

        return new \Bazalt\ORM\Collection($q);
    }

    public function toArray()
    {
        $res = parent::toArray();

        $res['is_published'] = $res['is_published'] == '1';
        $res['image'] = [
            'url' => $this->image
        ];
        $res['price'] = (int)$this->price;
        try {
            $res['image']['thumbnailUrl'] = thumb($this->image, '200x200');
            $res['image']['thumbnails'] = [
                'preview' => thumb($this->image, '96x96')
            ];
        } catch (\Exception $e) {

        }
        return $res;
    }
}