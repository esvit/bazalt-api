<?php

namespace Components\Menu\Webservice;

use Bazalt\Rest\Response;

use Components\Menu\Model\Element;

/**
 * @uri /menu/:item_id
 */
class ElementsResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function getElements($item_id)
    {
        $user = \Bazalt\Auth::getUser();
        $element = Element::getById((int)$item_id);
        if (!$element) {
            return new Response(Response::NOTFOUND, ['id' => 'Menu not found']);
        }
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $items = $element->toArray();

        $filterItems = function(&$items) use (&$filterItems) {
            foreach ($items as $key => $item) {
                if ($item['is_published'] != '1') {
                    unset($items[$key]);
                    $items = array_values($items); // потрібно щоб нумерація масива завжди була послідовна,
                                                   // якщо це не зробити, то json_encode перетворить цей масив в об'єкт
                } else {
                    $filterItems($item['children']);
                }
            }
        };
        if (!isset($_GET['all'])) {
            $filterItems($items['children']);
        }
        return new Response(Response::OK, $items);
    }

    /**
     * Create menu element in menu
     *
     * @method PUT
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function createOrMoveMenuElement($item_id)
    {
        $data = (array)$this->request->data;
        $data['item_id'] = $item_id;
        $data['insert'] = isset($_GET['insert']) ? $_GET['insert'] : 'false';
        $data['move'] = isset($_GET['move']) ? $_GET['move'] : 'false';
        $data['before'] = isset($_GET['before']) ? $_GET['before'] : 'false';
        $data = \Bazalt\Data\Validator::create($data);

        $data->field('insert')->bool();
        $data->field('move')->bool();

        $element = null;
        $prevElement = null;

        $data->field('item_id')->required()->validator('exist_element', function($value) use (&$element) {
            $element = Element::getById((int)$value);
            
            return ($element != null);
        }, "Menu element dosn't exists");

        $isInserting = $data['insert'] == 'true';
        $isMoving = $data['move'] == 'true';

        if ($isMoving) {
            $data->field('before')->required()->validator('exist_parent', function($value) use (&$element, &$prevElement) {
                $prevElement = Element::getById((int)$value);

                return ($prevElement != null) && ($prevElement->site_id == $element->site_id) && ($prevElement->root_id && $element->root_id);
            }, "Menu element dosn't exists");
        }
        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        if ($isMoving) {
            if ($isInserting) {
                if (!$prevElement->Elements->moveIn($element)) {
                    throw new \Exception('Error when procesing menu operation: 1');
                }
            } else {
                if (!$prevElement->Elements->moveAfter($element)) {
                    throw new \Exception('Error when procesing menu operation: 2');
                }
            }
            $newElement = $element;
        } else {
            $newElement = Element::create($element->root_id);
            $newElement->title = 'New item';

            // insert as first element
            if ($isInserting) {
                if (!$element->Elements->insert($newElement)) {
                    throw new \Exception('Insert failed: 2');
                }
            } else {
                if (!$element->Elements->insertAfter($newElement)) {
                    throw new \Exception('Insert failed: 3');
                }
            }
        }

        //$newElement->Childrens = array();
        //$this->view->assign('menu_components', ComMenu_Model_Menu::getMenuTypes());
        //$this->view->assign('menuitem', $element);
        //$settings = $this->view->fetch('admin/element.menuitem');

        return new Response(200, $newElement->toArray());
    }

    /**
     * @method DELETE
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function deleteMenuItem($item_id)
    {
        $menu = Element::getById((int)$item_id);

        if (!$menu) {
            return new Response(400, ['id' => "Menu item not found"]);
        }
        $menu->Elements->removeAll();
        if ($menu->depth == 0) {
            $menu->delete();
        } else {
            $menu->Elements->getParent()->Elements->remove($menu);
        }
        return new Response(200, $menu);
    }

    /**
     * @method POST
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function updateMenu()
    {
        $data = \Bazalt\Data\Validator::create($this->request->data);

        $menu = null;
        $data->field('id')->required()->validator('exist_menu', function($value) use (&$menu) {
            $menu = Element::getById((int)$value);

            return ($menu != null);
        }, "Menu dosn't exists");

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }
        $menu->title = $data['title'];
        $menu->description = $data['description'];
        $menu->menuType = $data['menuType'];
        $menu->settings = $data['settings'];
        $menu->is_published = $data['is_published'] ? '1' : '0';
        $menu->save();

        return new Response(200, $menu->toArray());
    }
}
