<?php

namespace Components\Users\Model\Base;

abstract class Gift extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_users_gifts';

    const MODEL_NAME = 'Components\\Users\\Model\\Gift';

    const ENGINE = 'InnoDB';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME, self::ENGINE);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('price', 'U:double(10)');
        $this->hasColumn('image', 'varchar(255)');
    }

    public function initRelations()
    {
        $this->hasRelation('Users', new \Bazalt\ORM\Relation\Many2Many('Bazalt\\Auth\\Model\\User', 'gift_id', 'Components\\Users\\Model\\GiftRefUser', 'user_id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\\Site\\ORM\\Localizable', ['title', 'body']);
    }
}