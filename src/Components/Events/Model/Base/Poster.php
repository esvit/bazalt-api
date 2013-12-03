<?php

namespace Components\Events\Model\Base;

abstract class Poster extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_events_posters';

    const MODEL_NAME = 'Components\\Events\\Model\\Poster';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PA:int(10)');
        $this->hasColumn('user_id', 'N:int(10)');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('company_title', 'N:varchar(255)');
        $this->hasColumn('type', 'N:varchar(255)');
        $this->hasColumn('body', 'N:longtext');
        $this->hasColumn('is_published', 'U:tinyint(1)|0');
        $this->hasColumn('image', 'N:varchar(255)');
        $this->hasColumn('start_date', 'N:varchar(255)');
        $this->hasColumn('end_date', 'N:varchar(255)');
        $this->hasColumn('hits', 'N:int(10)');
    }

    public function initRelations()
    {
        $this->hasRelation('User', new \Bazalt\ORM\Relation\One2One('News\Model\User', 'user_id', 'id'));

        $this->hasRelation('Images', new \Bazalt\ORM\Relation\One2Many('News\Model\Image', 'id', 'news_id'));
        $this->hasRelation('Category', new \Bazalt\ORM\Relation\One2One('News\Model\Category', 'category_id', 'id'));
        $this->hasRelation('Region', new \Bazalt\ORM\Relation\One2One('News\Model\Region', 'region_id', 'id'));

        $this->hasRelation('Tags', new \Bazalt\ORM\Relation\Many2Many('News\Model\Tag', 'news_id', 'News\Model\ArticleRefTag', 'tag_id'));
        $this->hasRelation('Comments', new \Bazalt\ORM\Relation\One2Many('News\Model\Comment', 'id', 'news_id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\ORM\Plugin\Timestampable', ['created' => 'created_at', 'updated' => 'updated_at']);

        $this->hasPlugin('Bazalt\Search\ElasticaPlugin', [
            'type' => self::TABLE_NAME
        ]);
    }
}