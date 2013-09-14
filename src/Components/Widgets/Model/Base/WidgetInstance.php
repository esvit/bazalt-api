<?php

namespace Components\Widgets\Model\Base;

abstract class WidgetInstance extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'cms_widgets_instances';

    const MODEL_NAME = 'Components\\Widgets\\Model\\WidgetInstance';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'U:int(10)');
        $this->hasColumn('widget_id', 'U:int(10)');
        $this->hasColumn('template', 'N:varchar(255)');
        $this->hasColumn('widget_template', 'N:varchar(255)');
        $this->hasColumn('config', 'mediumtext');
        $this->hasColumn('position', 'varchar(60)');
        $this->hasColumn('order', 'U:int(10)');
        $this->hasColumn('publish', 'U:tinyint(1)');
    }

    public function initRelations()
    {
        $this->hasRelation('Widget', new \Bazalt\ORM\Relation\One2One('Components\\Widgets\\Model\\Widget', 'widget_id', 'id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\\ORM\\Plugin\\Serializable', 'config');
    }
}