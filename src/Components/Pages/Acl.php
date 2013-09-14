<?php

namespace Components\Pages;

class Acl implements \Bazalt\Auth\Acl\Container
{
    const ACL_CAN_MANAGE_SELF_PAGES = 1;

    const ACL_CAN_MANAGE_ALL_PAGES = 2;
    //const ACL_CAN_WRITE_NEWS = 4;

    public function getAclLevels()
    {
        return [
            'can_manage_self' => self::ACL_CAN_MANAGE_SELF_PAGES,
            'can_manage_all'  => self::ACL_CAN_MANAGE_ALL_PAGES
        ];
    }

    public function getUserLevels(\Bazalt\Auth\Model\User $user, &$levels)
    {
        if ($user->is_god) {
            $levels['pages'] = 255;
            return;
        }
        $levels['pages'] = 0;
    }
}