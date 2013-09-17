<?php

namespace Components\Files\Model\Base;

abstract class File extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_filestorage_fs';

    const MODEL_NAME = 'Components\\Files\\Model\\File';

    const ENGINE = 'InnoDB';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME, self::ENGINE);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'U:int(10)|0');
        $this->hasColumn('name', 'varchar(255)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('alias', 'N:varchar(255)');
        $this->hasColumn('path', 'N:varchar(255)|NULL');
        $this->hasColumn('mimetype', 'N:varchar(60)|NULL');
        $this->hasColumn('is_system', 'U:tinyint(3)|0');
        $this->hasColumn('user_id', 'UN:int(10)');
        $this->hasColumn('access', 'UN:int(10)');
        $this->hasColumn('downloads', 'UN:int(10)');
        $this->hasColumn('size', 'UN:int(10)');
        $this->hasColumn('width', 'UN:int(10)');
        $this->hasColumn('height', 'UN:int(10)');
        $this->hasColumn('component_id', 'UN:int(10)');
    }

    public function initRelations()
    {
        $this->hasRelation('Elements', new \Bazalt\ORM\Relation\NestedSet('Components\\Files\\Model\\File', 'site_id'));
        $this->hasRelation('Items', new \Bazalt\ORM\Relation\NestedSet('Components\\Files\\Model\\File', 'site_id'));
        $this->hasRelation('Folders', new \Bazalt\ORM\Relation\NestedSet('Components\\Files\\Model\\File', 'site_id', null, array('mimetype' => 'directory')));
    }
    
    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\\Site\\ORM\\Localizable', ['title','body']);

        $this->hasPlugin('Bazalt\\ORM\\Plugin\\Timestampable', ['created' => 'created_at', 'updated' => 'updated_at']);
    }
}