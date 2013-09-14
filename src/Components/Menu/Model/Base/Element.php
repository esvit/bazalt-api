<?php

namespace Components\Menu\Model\Base;

abstract class Element extends \Bazalt\ORM\Record
{
    use \Bazalt\Site\ORM\LocalizableTrait;

    const TABLE_NAME = 'com_menu_elements';

    const MODEL_NAME = 'Components\\Menu\\Model\\Element';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'U:int(10)');
        $this->hasColumn('root_id', 'UN:int(10)');
        $this->hasColumn('menuType', 'N:varchar(30)');
        $this->hasColumn('settings', 'N:text');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('description', 'varchar(255)');
        $this->hasColumn('is_published', 'U:tinyint(3)');
    }

    public function initRelations()
    {
        $this->hasRelation('PublicElements', new \Bazalt\ORM\Relation\NestedSet(self::MODEL_NAME, 'root_id', 'id', ['is_publish' => 1]));
        $this->hasRelation('Elements', new \Bazalt\ORM\Relation\NestedSet(self::MODEL_NAME, 'root_id', 'id'));
    }
    
    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\\ORM\\Plugin\\Serializable', 'settings');

        $this->hasPlugin('Bazalt\\Site\\ORM\\Localizable', ['title', 'description']);
    }
}