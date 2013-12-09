<?php

namespace Components\Events\Model;

class Adm extends Base\Adm
{
    public static function create()
    {
        $user = \Bazalt\Auth::getUser();
        $adm = new Adm();
        if (!$user->isGuest()) {
            $adm->user_id = $user->id;
        }
        return $adm;
    }
}