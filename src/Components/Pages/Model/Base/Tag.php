<?php

namespace Components\Pages\Model\Base;

abstract class Tag extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_pages_tags';

    const MODEL_NAME = 'Components\\Pages\\Model\\Tag';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'U:int(10)');
        $this->hasColumn('title', 'varchar(10)');
        $this->hasColumn('url', 'varchar(10)');
        $this->hasColumn('quantity', 'U:int(10)');
        $this->hasColumn('is_published', 'U:tinyint(10)');
    }

    public function initRelations()
    {
        $this->hasRelation('Pages', new \Bazalt\ORM\Relation\Many2Many(
            'Components\\Pages\\Model\\Page', 'tag_id', 'Components\\Pages\\Model\\TagRefPage', 'page_id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\\ORM\\Plugin\\Timestampable', ['created' => 'created_at', 'updated' => 'updated_at']);
    }
}