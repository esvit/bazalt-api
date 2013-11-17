<?php

namespace Components\Seo\Model\Base;

abstract class Page extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_seo_pages';

    const MODEL_NAME = 'Components\\Seo\\Model\\Page';

    const ENGINE = 'InnoDB';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME, self::ENGINE);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'UN:int(10)');
        $this->hasColumn('url', 'varchar(255)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('keywords', 'varchar(255)');
        $this->hasColumn('description', 'varchar(255)');
        $this->hasColumn('article', 'text');
        $this->hasColumn('redirect_to', 'varchar(255)');
    }

    public function initRelations()
    {
    }
}