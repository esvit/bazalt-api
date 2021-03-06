<?php

namespace Components\Users\Model;

class Message extends Base\Message
{
    public static function create()
    {
        $m = new Message();
        $m->from_id = \Bazalt\Auth::getUser()->id;
        return $m;
    }

    public static function isFirst($userId)
    {
        $q = Message::select()
                ->andWhere('to_id = ?', $userId)
                ->andWhere('from_id = ?', \Bazalt\Auth::getUser()->id);

        return $q->fetch() == null;
    }

    public static function getCollection()
    {
        $q = Message::select();
        $q->orderBy('created_at DESC');

        return new \Bazalt\ORM\Collection($q);
    }

    public static function getUnreadedCount($userId, $isModerated = 1)
    {
        $q = Message::select('COUNT(*) AS cnt');
        if ($isModerated) {
            $q->where('to_id = ?', $userId);
        }
        $q->andWhere('is_moderated = ?', $isModerated)
            ->andWhere('is_readed = ?', 0);

        return $q->fetch()->cnt;
    }

    public function toArray()
    {
        $res = parent::toArray();

        $res['from'] = $this->FromUser->toArray();
        $res['to'] = $this->ToUser->toArray();
        $res['is_moderated'] = $this->is_moderated == '1';

        return $res;
    }
}