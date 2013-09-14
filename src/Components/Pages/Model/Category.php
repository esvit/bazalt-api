<?php

namespace Components\Pages\Model;

use Bazalt\ORM;

class Category extends Base\Category
{
    public static function create()
    {
        $category = new Category();
        $category->site_id = \Bazalt\Site::getId();
        $category->is_published = 0;

        return $category;
    }

    public static function getCollection()
    {
        $q = ORM::select('Components\\Pages\\Model\\Category c', 'c.*')
            ->andWhere('c.site_id = ?', \Bazalt\Site::getId())
            ->andWhere('c.is_published = ?', 1);

        return new ORM\Collection($q);
    }

    public static function searchByTitle($title)
    {
        $q = ORM::select('Components\\Pages\\Model\\Category c', 'c.*')
            ->innerJoin('Components\\Pages\\Model\\CategoryLocale cl', ['id', 'c.id'])
            ->where('cl.title LIKE ?', $title . "%")
            ->andWhere('c.site_id = ?', \Bazalt\Site::getId());

        return new ORM\Collection($q);
    }

    public function getUrl()
    {
        if (empty($this->url)) {
            $this->url = cleanUrl(translit($this->title['en']));
            $this->save();
        }
        return '/c-' . $this->url;
        //return Routing\Route::urlFor('Pages.Page', array('page' => $this));
    }

    public static function getSiteRootCategory($siteId = null)
    {
        if (!$siteId) {
            $siteId = \Bazalt\Site::getId();
        }
        $q = ORM::select('Components\Pages\Model\Category c')
            ->where('c.site_id = ?', $siteId)
            ->andWhere('c.depth = 0');

        $category = $q->fetch();
        if (!$category) {
            $category = Category::create();
            $category->site_id = $siteId;
            $category->lft = 1;
            $category->rgt = 2;
            $category->depth = 0;
            $category->save();
        }
        return $category;
    }

    public function getSubcategories($depth = 1)
    {
        return $this->PublicElements->get($depth);
    }

    public static function getByUrl($alias, $category = null, $siteId = null)
    {
        if (!$siteId) {
            $siteId = \Bazalt\Site::getId();
        }
        $q = ORM::select('Components\\Pages\\Model\\Category r')
            ->andWhere('r.site_id = ?', $siteId)
            ->andWhere('r.url = ?', strToLower($alias));

        if ($category != null) {
            if(is_numeric($category)) {
                $q->andWhere('r.site_id = ?', (int)$category);
            } elseif ($category instanceof \Components\Pages\Model\Category) {
                $q->andWhere('r.site_id = ?', $category->site_id);
                $q->andWhere('r.lft > ?', $category->lft);
                $q->andWhere('r.rgt < ?', $category->rgt);
            }
        }
        return $q->limit(1)->fetch();
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
        if (!isset($res['config'])) {
            $res['config'] = new \stdClass();
        }
        $res['$url'] = '/c-' . $this->url;
        return $res;
    }
}