<?php

namespace Components\Users\Model\Base;

abstract class GiftLocale extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_users_gifts_locale';

    const MODEL_NAME = 'Components\\Users\\Model\\GiftLocale';

    const ENGINE = 'InnoDB';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME, self::ENGINE);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('lang_id', 'varchar(2)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('body', 'mediumtext');
    }

    public function initRelations()
    {
    }

    public function initPlugins()
    {
    }
}