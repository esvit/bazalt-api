<?php

namespace Components\Widgets\Model;

use Bazalt\ORM;

class Widget extends Base\Widget
{
    public static function getActiveWidgets()
    {
        $q = self::getActiveCollection();

        /**
         * @var $widgets Widget[]
         */
        $widgets = $q->fetchAll();
        foreach ($widgets as $k => $widget) {
            $config = $widget->getEmptyWidget();
            if (!$config->isAvaliable()) {
                unset($widgets[$k]);
            }
        }
        return $widgets;
    }

    /**
     * @return \Bazalt\ORM\Collection
     */
    public static function getCollection()
    {
        $q = Widget::select()
                ->where('is_active = ?', 1)
                ->andWhere('site_id = ? OR site_id IS NULL', \Bazalt\Site::getId());

        return new \Bazalt\ORM\Collection($q);
    }

    public static function getByClassName($className)
    {
        $q = Widget::select()
                ->where('LOWER(className) = ?', strToLower($className))
                ->limit(1);

        return $q->fetch();
    }

    public static function getInstancesForTemplate($template, $position)
    {
        $q = ORM::select('Framework\CMS\Model\WidgetInstance c', 'c.*')
                ->innerJoin('Framework\CMS\Model\Widget w', array('id', 'c.widget_id'))
                ->where('c.template = ?', $template)
                ->andWhere('c.position = ?', $position)
                ->andWhere('c.site_id = ?', \Bazalt\Site::getId())
                ->andWhere('w.is_active = 1')
                ->orderBy('c.`order`');

        if (!\Bazalt\Auth::getUser()->hasRight(null, \Bazalt\Auth::ACL_CAN_ADMIN_WIDGETS)) {
            $q->andWhere('c.publish = 1');
        }

        return $q->fetchAll();
    }

    public static function getWidgets()
    {
        $q = Widget::select();

        return $q->fetchAll();
    }

    public function getEmptyConfig()
    {
        $config = WidgetInstance::create();
        $config->widget_id = $this->id;

        return $config;
    }

    public function getEmptyWidget()
    {
        $config = $this->getEmptyConfig();
        $widget = $config->getWidgetInstance();

        return $widget;
    }
}