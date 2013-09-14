<?php

namespace Components\Menu;

use \Framework\CMS as CMS,
    \Bazalt\Routing\Route;

class Component extends CMS\Component implements CMS\Menu\HasItems
{
    public static function getName()
    {
        return 'Menu';
    }

    public function initComponent(CMS\Application $application)
    {
        if ($application instanceof \App\Site\Application) {
            $application->registerJsComponent('Component.Menu', relativePath(__DIR__ . '/component.js'));
        } else {
            $application->registerJsComponent('Component.Menu.Admin', relativePath(__DIR__ . '/admin.js'));
        }
    }

    public function getMenuTypes()
    {
        return [
            'Components\Menu\Menu\Link',
            'Components\Menu\Menu\MainPage'
        ];
    }
}