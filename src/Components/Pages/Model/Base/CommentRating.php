<?php

namespace Components\Pages\Model\Base;

abstract class CommentRating extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_pages_comments_ratings';

    const MODEL_NAME = 'Components\\Pages\\Model\\CommentRating';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('comment_id', 'U:int(10)');
        $this->hasColumn('user_id', 'U:int(10)');
        $this->hasColumn('ip', 'U:int(10)');
        $this->hasColumn('browser_id', 'U:int(10)');
        $this->hasColumn('useragent', 'N:varchar(500)');
        $this->hasColumn('created_at', 'datetime');
        $this->hasColumn('rating', 'U:int(10)');
    }

    public function initRelations()
    {
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\\ORM\\Plugin\\Timestampable', ['created' => 'created_at']);
    }
}