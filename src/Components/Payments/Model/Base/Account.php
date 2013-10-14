<?php

namespace Components\Payments\Model\Base;

abstract class Account extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_payments_accounts';

    const MODEL_NAME = 'Components\\Payments\\Model\\Account';

    const ENGINE = 'InnoDB';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME, self::ENGINE);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('type_id', 'U:int(10)');
        $this->hasColumn('user_id', 'U:int(10)');
        $this->hasColumn('state', 'decimal(10)');
    }

    public function initRelations()
    {
        $this->hasRelation('User', new \Bazalt\ORM\Relation\One2One('Bazalt\\Auth\\Model\\User', 'user_id', 'id'));
        $this->hasRelation('AccountType', new \Bazalt\ORM\Relation\One2One('Components\\Payments\\Model\\AccountType', 'type',  'id'));
        $this->hasRelation('Transaction', new \Bazalt\ORM\Relation\One2Many('Components\\Payments\\Model\\Transaction', 'id', 'account_id'));
    }

    public function initPlugins()
    {
    }
}