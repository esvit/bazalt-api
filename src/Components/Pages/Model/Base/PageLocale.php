<?php
/**
 * Data model
 *
 * @category  DataModel
 * @package   DataModel
 * @author    DataModel Generator v1.3
 * @version   Revision: $Rev: 2 $
 */
/**
 * Data model for table "com_articles_articles_locale"
 *
 * @category  DataModel
 * @package   DataModel
 * @author    DataModel Generator v1.3
 * @version   Revision: $Rev: 2 $
 *
 * @property-read mixed Id
 * @property-read mixed LangId
 * @property-read mixed Title
 */
namespace Components\Pages\Model\Base;

abstract class PageLocale extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'com_pages_pages_locale';

    const MODEL_NAME = 'Components\Pages\Model\PageLocale';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PU:int(10)');
        $this->hasColumn('lang_id', 'PU:int(10)');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('body', 'N:text');
        $this->hasColumn('completed', 'U:tinyint(4)|0');
    }

    public function initRelations()
    {

    }
}