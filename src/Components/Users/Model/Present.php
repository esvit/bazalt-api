<?php

namespace Components\Users\Model;

class Present extends Base\Present
{
    public static function getCollection()
    {
        $q = Present::select();

        return new \Bazalt\ORM\Collection($q);
    }
}