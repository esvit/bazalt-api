<?php

namespace Components\Users\Model\Base;

abstract class Message extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'cms_users_messages';

    const MODEL_NAME = 'Components\\Users\\Model\\Message';

    const ENGINE = 'InnoDB';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME, self::ENGINE);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('from_id', 'U:int(10)');
        $this->hasColumn('to_id', 'U:int(10)');
        $this->hasColumn('message', 'mediumtext');
        $this->hasColumn('is_readed', 'tinyint(10)');
        $this->hasColumn('is_deleted', 'tinyint(10)');
    }

    public function initRelations()
    {
        $this->hasRelation('FromUser', new \Bazalt\ORM\Relation\One2One('Bazalt\\Auth\\Model\\User', 'from_id', 'id'));
        $this->hasRelation('ToUser', new \Bazalt\ORM\Relation\One2One('Bazalt\\Auth\\Model\\User', 'to_id', 'id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\\ORM\\Plugin\\Timestampable', ['created' => 'created_at']);
    }
}