<?php

namespace Components\Menu\Model\Base;

abstract class ElementLocale extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_menu_elements_locale';

    const MODEL_NAME = 'Components\Menu\Model\ElementLocale';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PU:int(10)');
        $this->hasColumn('lang_id', 'PU:int(10)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('description', 'varchar(255)');
        $this->hasColumn('completed', 'tinyint(3):1');
    }

    public function initRelations()
    {

    }
}