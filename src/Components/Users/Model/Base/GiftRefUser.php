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
        $this->hasColumn('item_id', 'PU:int(10)');
        $this->hasColumn('gift_id', 'U:int(10)');
        $this->hasColumn('user_id', 'U:int(10)');
        $this->hasColumn('message', 'text');
        $this->hasColumn('status', 'U:tinyint(10)');
    }

    public function initRelations()
    {
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\\ORM\\Plugin\\Timestampable', ['created' => 'created_at', 'updated' => 'updated_at']);
    }
}