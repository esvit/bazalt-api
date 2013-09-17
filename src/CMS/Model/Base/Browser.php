<?php

namespace CMS\Model\Base;

abstract class Browser extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'cms_browsers';

    const MODEL_NAME = 'CMS\\Model\\Browser';

    const ENGINE = 'InnoDB';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME, self::ENGINE);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('useragent', 'varchar(500)');
        $this->hasColumn('browser', 'varchar(255)');
        $this->hasColumn('platform', 'varchar(255)');
        $this->hasColumn('is_bot', 'U:tinyint(1)');
    }

    public function initRelations()
    {
    }

    public static function getById($id)
    {
        return parent::getRecordById($id, self::MODEL_NAME);
    }

    public static function getAll($limit = null)
    {
        return parent::getAllRecords($limit, self::MODEL_NAME);
    }

    public static function select($fields = null)
    {
        return ORM::select(self::MODEL_NAME, $fields);
    }

    public static function insert($fields = null)
    {
        return ORM::insert(self::MODEL_NAME, $fields);
    }
}