<?php

namespace Components\Users\Model;

class Message extends Base\Message
{
    public static function getUserIncoming(\Bazalt\Auth\Model\User $user)
    {
        $q = Message::select();
        $q->where('to_id = ?', $user->id)
          ->orderBy('created_at DESC');

        return new \Bazalt\ORM\Collection($q);
    }

    public function toArray()
    {
        $res = parent::toArray();

        $res['from'] = $this->FromUser->toArray();

        return $res;
    }
}