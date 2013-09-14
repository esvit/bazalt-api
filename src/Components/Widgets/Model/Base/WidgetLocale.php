<?php

namespace Components\Widgets\Model\Base;

abstract class WidgetLocale extends \Bazalt\ORM\Record
{
    const TABLE_NAME = "cms_widgets_locale";

    const MODEL_NAME = "Components\\Widgets\\Model\\WidgetLocale";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PU:int(10)|0');
        $this->hasColumn('lang_id', 'PU:varchar(2)');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('description', 'N:text');
        $this->hasColumn('completed', 'U:tinyint(4)|0');
    }

    public function initRelations()
    {
    }
}