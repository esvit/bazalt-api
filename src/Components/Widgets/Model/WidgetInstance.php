<?php

namespace Components\Widgets\Model;

class WidgetInstance extends Base\WidgetInstance
{
    public function getName()
    {
        return $this->Widget->title;
    }

    public static function create()
    {
        $widget = new WidgetInstance();
        $widget->site_id = \Bazalt\Site::getId();

        return $widget;
    }

    public function getWidgetInstance()
    {
        return Widget::getWidgetInstance($this);
    }
}