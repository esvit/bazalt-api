<?php

namespace Components\Menu\Webservice;

use \Bazalt\Rest\Response,
    \Bazalt\Session,
    \Bazalt\Data as Data;

use Components\Menu\Model\Element;
use Whoops\Example\Exception;

/**
 * @uri /menu
 */
class MenuResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function getMenus()
    {
        $user = \Bazalt\Auth::getUser();
        $menus = Element::getRoots();
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $result = [];
        foreach ($menus as $k => $menu) {
            $result[$k] = $menu->toArray();
            unset($result[$k]['children']);
        }
        return new Response(200, $result);
    }

    /**
     * @method POST
     * @priority 10
     * @action getSettings
     * @provides application/json
     * @json
     * @return \Tonic\Response
    public function getSettings()
    {
        $data = new Data\Validator((array)$this->request->data);

        $menu = null;
        // 1. Check menu
        $data->field('id')->required()->validator('exist_menu', function($value) use (&$menu) {
            $menu = Element::getById((int)$value);
            
            return ($menu != null);
        }, "Menu dosn't exists");

        // 2. Check menu type
        $data->field('menuType')->required()->validator('exist_menuType', function($value) use (&$menu) {
            $types = Element::getMenuTypes();

            if (!isset($types[$value])) {
                return false;
            }
            $item = $types[$value];
            $menu->menuType = $value;
            $menu->component_id = $item->component()->config()->id;

            return true;
        }, "Menu type dosn't exists");

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }
        return new Response(200, $menu);
    }
     */

    /**
     * @method PUT
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function createMenu()
    {
        $data = Data\Validator::create($this->request->data);

        $menu = Element::create();
        $menu->title = $data['title'];
        $menu->save();
        $menu->root_id = $menu->id;
        $menu->save();

        return new Response(200, $menu->toArray());
    }
}
