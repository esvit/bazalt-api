<?php

namespace Components\Users\Model;

class Gift extends Base\Gift
{
    public static function getCollection()
    {
        $q = Gift::select();

        return new \Bazalt\ORM\Collection($q);
    }
}