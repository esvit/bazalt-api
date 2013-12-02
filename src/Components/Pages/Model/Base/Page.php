<?php

namespace Components\Pages\Model\Base;

abstract class Page extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_pages_pages';

    const MODEL_NAME = 'Components\\Pages\\Model\\Page';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PA:int(10)');
        $this->hasColumn('site_id', 'U:int(10)');
        $this->hasColumn('user_id', 'N:int(10)');
        $this->hasColumn('category_id', 'UN:int(10)');
        $this->hasColumn('url', 'N:varchar(255)');
        $this->hasColumn('template', 'N:varchar(255)');
        $this->hasColumn('status', 'U:tinyint(1)|0');
        $this->hasColumn('is_allow_comments', 'U:tinyint(1)|0');
        $this->hasColumn('is_top', 'U:tinyint(1)|0');
        $this->hasColumn('hits', 'UN:int(10)');
        $this->hasColumn('comments_count', 'UN:int(10)');
        $this->hasColumn('rating', 'UN:int(10)');
    }

    public function initRelations()
    {
        $this->hasRelation('Category', new \Bazalt\ORM\Relation\One2One('Components\\Pages\\Model\\Category', 'category_id', 'id'));
        $this->hasRelation('Videos', new \Bazalt\ORM\Relation\One2Many('Components\\Pages\\Model\\Video', 'id', 'page_id'));
        $this->hasRelation('Images', new \Bazalt\ORM\Relation\One2Many('Components\\Pages\\Model\\Image', 'id', 'page_id'));
        $this->hasRelation('User', new \Bazalt\ORM\Relation\One2One('Bazalt\\Auth\\Model\\User', 'user_id', 'id'));
        $this->hasRelation('Tags', new \Bazalt\ORM\Relation\Many2Many(
            'Components\\Pages\\Model\\Tag', 'page_id', 'Components\\Pages\\Model\\TagRefPage', 'tag_id'));

        $this->hasRelation('CommentsRoot', new \Bazalt\ORM\Relation\One2One('Components\\Pages\\Model\\Comment', 'page_id', 'id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\\Site\\ORM\\Localizable', ['title', 'body']);
        $this->hasPlugin('Bazalt\\ORM\\Plugin\\Timestampable', ['created' => 'created_at', 'updated' => 'updated_at']);

        if (!TESTING_STAGE) {
        //    $this->hasPlugin('Bazalt\Search\ElasticaPlugin', [
        //        'type' => self::TABLE_NAME
            ]);
        }
    }
}