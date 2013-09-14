<?php

namespace Components\Pages\Model\Base;

abstract class Comment extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_pages_comments';

    const MODEL_NAME = 'Components\\Pages\\Model\\Comment';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('news_id', 'U:int(10)');
        $this->hasColumn('body', 'text');
        $this->hasColumn('created_at', 'datetime');

        // Ідентифікація користувача
        $this->hasColumn('user_name', 'N:varchar(100)');
        $this->hasColumn('email', 'N:varchar(100)');
        //$this->hasColumn('browser_id', 'U:int(10)');
        $this->hasColumn('browser_agent', 'N:varchar(255)');
        $this->hasColumn('ip', 'U:int(10)');
        $this->hasColumn('user_id', 'U:int(10)');

        $this->hasColumn('is_deleted', 'U:tinyint(1)|0');
        $this->hasColumn('is_moderated', 'U:tinyint(1)|0');

        // NestedSet
        $this->hasColumn('lft', 'U:int(10)');
        $this->hasColumn('rgt', 'U:int(10)');
        $this->hasColumn('depth', 'U:int(10)');
    }

    public function initRelations()
    {
        $this->hasRelation('Elements', new \Bazalt\ORM\Relation\NestedSet('Components\\Pages\\Model\\Comment', 'page_id'));
        $this->hasRelation('User', new \Bazalt\ORM\Relation\One2One('Bazalt\\Auth\\Model\\User', 'user_id',  'id'));
        $this->hasRelation('Article', new \Bazalt\ORM\Relation\One2One('Components\\Pages\\Model\\Page', 'page_id',  'id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\\ORM\\Plugin\\Timestampable', ['created' => 'created_at', 'updated' => 'updated_at']);

        /*$this->hasPlugin('Bazalt\\Search\\ElasticaPlugin', [
            'type' => self::TABLE_NAME
        ]);*/
    }
}