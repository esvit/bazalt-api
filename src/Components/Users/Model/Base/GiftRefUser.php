<?php

namespace Components\Users\Model\Base;

abstract class GiftRefUser extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_users_gifts_ref_users';

    const MODEL_NAME = 'Components\\Users\\Model\\GiftRefUser';

    const ENGINE = 'InnoDB';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME, self::ENGINE);
    }

    protected function initFields()
    {
        $this->hasColumn('gift_id', 'PU:int(10)');
        $this->hasColumn('user_id', 'PU:int(10)');
        $this->hasColumn('message', 'text');
    }

    public function initRelations()
    {
    }
}