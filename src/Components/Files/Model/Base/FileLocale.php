<?php

namespace Components\Files\Model\Base;

abstract class FileLocale extends \Bazalt\ORM\Record
{
    const TABLE_NAME = "com_filestorage_fs_locale";

    const MODEL_NAME = "Components\\Files\\Model\\FileLocale";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PU:int(10)|0');
        $this->hasColumn('lang_id', 'PU:varchar(2)');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('body', 'N:text');
        $this->hasColumn('completed', 'U:tinyint(4)|0');
    }

    public function initRelations()
    {
        $this->hasRelation('File', new \Bazalt\ORM\Relation\One2One(self::MODEL_NAME, 'id', 'Components\\Files\\Model\\File', 'id'));
    }
}