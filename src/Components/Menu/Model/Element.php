<?php

namespace Components\Menu\Model;

class Element extends Base\Element
{
    protected $menuItem = null;

    public static function create($menuId = null)
    {
        $item = new Element();
        $item->site_id = \Bazalt\Site::getId();
        $item->root_id = $menuId;
        $item->lft = 1;
        $item->rgt = 2;
        $item->is_published = false;
        return $item;
    }

    public function toArray()
    {
        $res = parent::toArray();
        $res['is_published'] = $this->is_published == 1;
        unset($res['Childrens']);
        $elements = $this->Elements->get();
        $count = 0;
        $toArray = function($items) use (&$toArray, &$count) {
            $result = [];
            foreach ($items as $key => $item) {
                $count++;
                $res = $item->toArray();
                $res['children'] = (is_array($item->Childrens) && count($item->Childrens)) ? $toArray($item->Childrens) : [];
                $result[$key] = $res;
            }
            return $result;
        };
        $res['children'] = $toArray($elements);
        $res['count'] = $count;
        if (!$res['settings']) {
            $res['settings'] = new \stdClass();
        }

        $res['url'] = '#';
        if ($this->menuType == 'bcPages.Menu.Page') {
            if (isset($this->settings->page_id)) {
                $page = \Components\Pages\Model\Page::getById($this->settings->page_id);
                if ($page) {
                    $res['url'] = $page->getUrl();
                }
            }
        }
        if ($this->menuType == 'bcPages.Menu.Category') {
            if (isset($this->settings->category_id)) {
                $category = \Components\Pages\Model\Category::getById($this->settings->category_id);
                if ($category) {
                    $res['url'] = $category->getUrl();
                }
            }
        }
        if ($this->menuType == 'Components.Menu.Menu.MainPage') {
            $res['url'] = '/';
        }
        if ($this->menuType == 'bcMenu.Menu.Link') {
            $res['url'] = isset($res['settings']->url) ? $res['settings']->url : '';
        }

        return $res;
    }

    public static function getRoots()
    {
        $q = Element::select()
                    ->where('depth = ?', 0)
                    ->andWhere('site_id = ?', \Bazalt\Site::getId());

        return $q->fetchAll();
    }

    public static function getComponentMenuType(CMS\Component $component, $menuClass, Element $element = null)
    {
        if (!$component || !($component instanceof CMS\Menu\HasItems)) {
            return null;
            //throw new \Exception('Component "' . $componentName . '" must implements Framework\CMS\Menu\HasItems interface');
        }

        $menuTypes = $component->getMenuTypes();
        if (!in_array($menuClass, $menuTypes) || !class_exists($menuClass)) {
            return null;
            //throw new \Exception('Menu type not found in component');
        }
        $menuItem = new $menuClass($component, $element);
        if (!($menuItem instanceof CMS\Menu\ComponentItem)) {
            throw new \Exception('Menu type must be instance of Framework\CMS\Menu\ComponentItem');
        }
        return $menuItem;
    }

    public static function getElementById($id, $menuId)
    {
        $q = Element::select()
                    ->where('id = ?', (int)$id)
                    ->andWhere('root_id = ?', (int)$menuId);

        return $q->fetch();
    }

    public function getTitle($lang)
    {
        $menuItem = $this->getTranslation($lang);
        if (!$menuItem) {
            return null;
        }
        return $menuItem->title;
    }

    public function getDescription($lang)
    {
        $menuItem = $this->getTranslation($lang);
        if (!$menuItem) {
            return null;
        }
        return $menuItem->description;
    }

    public function getUrl()
    {
        if (array_key_exists('url', $this->config)) {
            return $this->config['url'];
        }
        return '/';
    }

    public function isEmptyMenu()
    {
        return ($this->getMenuItem() == null);
    }

    public function getMenuType()
    {
        $menuItem = $this->getMenuItem();
        if ($menuItem) {
            return $menuItem->getItemType();
        }
        return null;
    }

    public function getMenuItem()
    {
        if (!$this->menuType || !($componentModel = $this->Component)) {
            return null;
        }
        if (!$this->menuItem) {
            $component = CMS\Bazalt::getComponent($componentModel->name);
            $this->menuItem = self::getComponentMenuType($component, $this->menuType, $this);
        }
        return $this->menuItem;
    }

    public static function getByComponentName($componentName)
    {
        $q = ORM::select()
            ->from('Components\Menu\Model\Element els')
            ->innerJoin('Framework\CMS\Model\Component c', array('id', 'els.component_id'))
            ->where('c.name = ?', $componentName);

        return $q->fetchAll();
    }

    public function getMenu($onlyPublish = true)
    {
        $addItemsToMenu = function(&$menu, $elements) use (&$addItemsToMenu)
        {
            if (!is_array($elements)) {
                return;
            }
            foreach ($elements as $menuitem) {
                $item = $menuitem->getMenuItem();

                if ($item != null) {
                    $item->prepare();
                    if($item->visible()) {
                        if (count($menuitem->Childrens) > 0) {
                            $addItemsToMenu($item, $menuitem->Childrens);
                        }
                        $menu->addMenuItem($item);
                    }
                }
            }
        };

        $menu = new CMS\Menu\Item();
        if ($onlyPublish) {
            $elements = $this->PublicElements->get();
        } else {
            $elements = $this->Elements->get();
        }
        $addItemsToMenu($menu, $elements);

        return $menu;
    }

    /**
     * Get menu types of all loaded components
     *
     * @throws \Exception
     * @return array Array of menu types
     */
    public static function getMenuTypes()
    {
        $menuTypes = array();
        /*$components = CMS\Bazalt::getComponents();

        foreach ($components as $component) {
            if ($component instanceof CMS\Menu\HasItems) {
                $classes = $component->getMenuTypes();

                foreach ($classes as $menuClass) {
                    if (!class_exists($menuClass)) {
                        continue;
                    }
                    $menuItem = new $menuClass($component);
                    if (!($menuItem instanceof CMS\Menu\ComponentItem)) {
                        throw new \Exception(sprintf('Menu must "%s" be instance of Framework\CMS\Menu\ComponentItem', $menuClass));
                    }
                    $menuTypes[$menuClass] = $menuItem;
                }
            }
        }*/
        return $menuTypes;
    }
}
