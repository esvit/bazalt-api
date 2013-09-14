<?php

namespace Components\Widgets\Model\Base;

abstract class Widget extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'cms_widgets';

    const MODEL_NAME = 'Components\\Widgets\\Model\\Widget';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('component_id', 'U:int(10)');
        $this->hasColumn('className', 'varchar(50)');
        $this->hasColumn('default_template', 'N:varchar(255)');
        $this->hasColumn('is_active', 'U:tinyint(1)|0');
    }

    public function initRelations()
    {
        $this->hasRelation('Instances', new \Bazalt\ORM\Relation\One2Many('Components\\Widgets\\Model\\WidgetInstance', 'id', 'widget_id'));
        $this->hasRelation('Component', new \Bazalt\ORM\Relation\One2One('Components\\Widgets\\Model\\Component', 'component_id', 'id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\\Site\\ORM\\Localizable', ['title', 'description']);
    }
}