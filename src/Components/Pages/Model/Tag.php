<?php

namespace Components\Pages\Model;

use Bazalt\ORM;

class Tag extends Base\Tag
{
    public static function create($title, $url)
    {
        $tag = new Tag();
        $tag->site_id = \Bazalt\Site::getId();
        $tag->title = $title;
        $tag->url = $url;
        $tag->quantity = 0;

        return $tag;
    }

    public static function decreaseQuantity(Page $page)
    {
        $qIds = ORM::select('Components\\Pages\\Model\\TagRefPage tp', 'tp.tag_id')
                ->andWhere('tp.page_id = ?', $page->id);

        $q = ORM::update('Components\\Pages\\Model\\Tag t')
                ->set('quantity = quantity - 1')
                ->whereIn('id', $qIds);

        return $q->exec();
    }

    public static function increaseQuantity($ids)
    {
        if (!count($ids)) {
            return false;
        }
        $q = ORM::update('Components\\Pages\\Model\\Tag t')
                ->set('quantity = quantity + 1')
                ->whereIn('id', $ids);

        return $q->exec();
    }

    public static function searchByTitle($title)
    {
        $q = ORM::select('Components\\Pages\\Model\\Tag t')
            ->where('t.title LIKE ?', $title . "%")
            ->andWhere('t.site_id = ?', \Bazalt\Site::getId());

        return new ORM\Collection($q);
    }

    /**
     * Get tag by url
     */
    public static function getByUrl($url, $is_published = null, $userId = null)
    {
        $q = Tag::select()
            ->where('url = ?', $url)
            ->andWhere('site_id = ?', \Bazalt\Site::getId());

        if ($is_published != null) {
            $q->andWhere('is_published = ?', $is_published);
        }
        if ($userId != null) {
            $q->andWhere('user_id = ?', $userId);
        }
        $q->limit(1);
        return $q->fetch();
    }

    public static function getCollection($is_publisheded = null)
    {
        $q = ORM::select('Components\\Pages\\Model\\Tag f', 'f.*')
            ->andWhere('f.site_id = ?', \Bazalt\Site::getId());

        if ($is_publisheded) {
            $q->andWhere('is_published = ?', 1);
        }
        return new \Bazalt\ORM\Collection($q);
    }

    public static function getPopularCollection()
    {
        $q = ORM::select('Components\\Pages\\Model\\Tag f', 'f.*')
            ->andWhere('f.site_id = ?', \Bazalt\Site::getId())
            ->andWhere('is_published = ?', 1)
            ->orderBy('quantity DESC');

        return new \Bazalt\ORM\Collection($q);
    }

    public function toArray()
    {
        $res = parent::toArray();
        unset($res['site_id']);
        unset($res['is_published']);

        return $res;
    }
}