<?php

namespace Components\Payments\Model\Base;

abstract class AccountType extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_payments_account_types';

    const MODEL_NAME = 'Components\\Payments\\Model\\AccountType';

    const ENGINE = 'InnoDB';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME, self::ENGINE);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'U:int(10)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('ratio', 'U:float(10)');
        $this->hasColumn('currency', 'varchar(25)');
    }

    public function initRelations()
    {
        $this->hasRelation('Accounts', new \Bazalt\ORM\Relation\One2Many('Components\\Payments\\Model\\Account', 'id', 'type'));
    }

    public function initPlugins()
    {
    }
}