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

    public static function getCollection()
    {
        $q = Message::select();
        $q->orderBy('created_at DESC');

        return new \Bazalt\ORM\Collection($q);
    }

    public function toArray()
    {
        $res = parent::toArray();

        $res['from'] = $this->FromUser->toArray();
        $res['to'] = $this->ToUser->toArray();

        return $res;
    }
}