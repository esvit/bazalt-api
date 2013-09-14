<?php

namespace Components\Pages\Model\Base;

abstract class Category extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_pages_categories';

    const MODEL_NAME = 'Components\\Pages\\Model\\Category';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'U:int(10)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('url', 'varchar(255)');
        $this->hasColumn('image', 'varchar(255)');
        $this->hasColumn('description', 'mediumtext');
        $this->hasColumn('is_hidden', 'U:tinyint(1)|0');
        $this->hasColumn('is_published', 'U:tinyint(1)');
    }

    public function initRelations()
    {
        $this->hasRelation('Elements', new \Bazalt\ORM\Relation\NestedSet('Components\\Pages\\Model\\Category', 'site_id'));
        $this->hasRelation('PublicElements',
            new \Bazalt\ORM\Relation\NestedSet('Components\\Pages\\Model\\Category', 'site_id', null, ['is_hidden' => '0', 'is_published' => 1]));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\\Site\\ORM\\Localizable', ['title', 'description']);
    }
}