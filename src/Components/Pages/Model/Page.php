<?php

namespace Components\Pages\Model;

use Bazalt\ORM;
//use Framework\Core\Helper\Url;

class Page extends Base\Page //implements \Bazalt\Routing\Sluggable
{
    /**
     * Create new page without saving in database
     */
    public static function create()
    {
        $page = new Page();
        $page->site_id = \Bazalt\Site::getId();
        //$page->user_id = \Bazalt\Auth::getUser()->id;
        return $page;
    }

    /*public function toUrl(\Bazalt\Routing\Route $route)
    {
        return $this->url;
    }*/

    /**
     * Get page by url
     */
    public static function getByUrl($url, $is_published = null, $userId = null)
    {
        $q = Page::select()
            ->where('url = ?', $url)
            ->andWhere('f.site_id = ?', \Bazalt\Site::getId());

        if ($is_published != null) {
            $q->andWhere('is_published = ?', $is_published);
        }
        if ($userId != null) {
            $q->andWhere('user_id = ?', $userId);
        }
        $q->limit(1);
        return $q->fetch();
    }

    public static function searchByTitle($title)
    {
        $q = ORM::select('Components\\Pages\\Model\\Page p', 'p.*')
            ->innerJoin('Components\\Pages\\Model\\PageLocale pl', ['id', 'p.id'])
            ->where('pl.title LIKE ?', $title . "%")
            ->andWhere('p.site_id = ?', \Bazalt\Site::getId())
            ->andWhere('is_published = ?', 1);

        return new ORM\Collection($q);
    }

    public static function deleteByIds($ids)
    {
        if(!is_array($ids)) {
            $ids = array($ids);
        }
        $q = ORM::delete('Components\Pages\Model\Page a')
            ->whereIn('a.id', $ids)
            ->andWhere('a.site_id = ?', \Bazalt\Site::getId());

        return $q->exec();
    }

    public static function getCollection($onlyPublished = null, Category $category = null)
    {
        $q = ORM::select('Components\Pages\Model\Page f', 'f.*')
           // ->innerJoin('Components\Pages\Model\PageLocale ref', array('id', 'f.id'))
           // ->where('ref.lang_id = ?', CMS\Language::getCurrentLanguage()->id)
            ->andWhere('f.site_id = ?', \Bazalt\Site::getId());

        if ($onlyPublished) {
            $q->andWhere('is_published = ?', 1);
        }
        if ($category) {
            $q->andWhere('category_id = ?', $category->id);
        }
        $q->orderBy('created_at DESC');
        return new \Bazalt\ORM\Collection($q);
    }

    public function getUrl()
    {
        if (empty($this->url) && isset($this->title['en'])) {
            $this->url = cleanUrl(translit($this->title['en']));
            $this->save();
        }
        return '/p-' . $this->url;
        //return Routing\Route::urlFor('Pages.Page', array('page' => $this));
    }

    public function toArray()
    {
        $res = parent::toArray();
        $res['is_published'] = $res['is_published'] == '1';
        $res['is_allow_comments'] = $res['is_allow_comments'] == '1';
        $res['rating'] = (int)$res['rating'];
        $res['url'] = $this->getUrl();

        if ($user = $this->User) {
            $res['user'] = [
                'id' => $user->id,
                'name' => $user->getName()
            ];
        }

        if ($category = $this->Category) {
            $res['breadcrumbs'] = [];
            $path = $this->Category->PublicElements->getPath();
            foreach ($path as $cat) {
                $data = $cat->toArray();
                unset($data['children']);
                $res['breadcrumbs'][] = $data;
            }
            if ($category->is_published && !$category->is_hidden) {
                $data = $category->toArray();
                unset($data['children']);
                $res['breadcrumbs'][] = $data;
            }
        }

        $res['tags'] = [];
        $tags = $this->Tags->get();
        foreach ($tags as $tag) {
            $res['tags'][] = $tag->toArray();
        }

        $res['images'] = [];
        $images = $this->Images->get();
        foreach ($images as $image) {
            $res['images'][] = $image->toArray();
        }
        return $res;
    }
}