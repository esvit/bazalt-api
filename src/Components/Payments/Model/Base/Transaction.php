<?php

namespace Components\Payments\Model\Base;

abstract class Transaction extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_payments_transactions';

    const MODEL_NAME = 'Components\\Payments\\Model\\Transaction';

    const ENGINE = 'InnoDB';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME, self::ENGINE);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('account_id', 'U:int(10)');
        $this->hasColumn('type', 'N:enum("up","down")');
        $this->hasColumn('start_date', 'timestamp|CURRENT_TIMESTAMP');
        $this->hasColumn('end_date', 'N:timestamp');
        $this->hasColumn('data', 'N:blob');
        $this->hasColumn('state', 'N:varchar(100)');
        $this->hasColumn('sum', 'U:int(10)');
        $this->hasColumn('period_state', 'decimal(10,2)');
        $this->hasColumn('comment', 'N:text');
    }

    public function initRelations()
    {
        $this->hasRelation('Account', new \Bazalt\ORM\Relation\One2One('Components\\Payments\\Model\\Account', 'account_id', 'id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\\ORM\\Plugin\\Serializable', 'data');
        $this->hasPlugin('Bazalt\\ORM\\Plugin\\Timestampable', ['created' => 'start_date']);
    }
}