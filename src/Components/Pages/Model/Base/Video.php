<?php

namespace Components\Pages\Model\Base;

abstract class Video extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_pages_videos';

    const MODEL_NAME = 'Components\Pages\Model\Video';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('page_id', 'U:int(10)');
        $this->hasColumn('url', 'N:varchar(255)');
        $this->hasColumn('image', 'N:varchar(255)');
        $this->hasColumn('sort_order', 'U:int(10)');
    }

    public function initRelations()
    {

    }
}