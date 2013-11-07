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

    public static function getUnreadedCount($userId)
    {
        $q = Message::select('COUNT(*) AS cnt');
        $q->where('to_id = ?', $userId)
            ->andWhere('is_moderated = ?', 1)
            ->andWhere('is_readed = ?', 0);

        return $q->fetch()->cnt;
    }

    public function toArray()
    {
        $res = parent::toArray();

        $res['from'] = $this->FromUser->toArray();
        $res['to'] = $this->ToUser->toArray();

        return $res;
    }
}