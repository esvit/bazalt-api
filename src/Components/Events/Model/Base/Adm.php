<?php

namespace Components\Events\Model\Base;

abstract class Adm extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'adm';

    const MODEL_NAME = 'Components\\Events\\Model\\Adm';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PA:int(10)');
        $this->hasColumn('user_id', 'N:int(10)');
        $this->hasColumn('email', 'N:varchar(255)');
        $this->hasColumn('ip', 'N:varchar(255)');
        $this->hasColumn('browser', 'N:varchar(255)');
        $this->hasColumn('phone', 'N:varchar(255)');
        $this->hasColumn('address', 'N:varchar(255)');
        $this->hasColumn('letter', 'N:varchar(255)');
    }

    public function initRelations()
    {
    }

    public function initPlugins()
    {
    }
}