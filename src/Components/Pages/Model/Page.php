<?php

namespace Components\Pages\Model;

use Bazalt\ORM;

class Page extends Base\Page
{
    /**
     * удаленная статья
     */
    const PUBLISH_STATE_DELETED = 0;
    /**
     * не промодерированая и не опубликованая, пользователь еще не пробывал публиковать статью
     */
    const PUBLISH_STATE_DRAFT = 1;
    /**
     * поданая на модерацию
     */
    const PUBLISH_STATE_NOT_MODERATED = 2;
    /**
     * промодерированая и не опубликованая, когда модератор запрещает публикацию
     */
    const PUBLISH_STATE_MODERATED = 3;
    /**
     * промодерированая и опубликованая
     */
    const PUBLISH_STATE_PUBLISHED = 4;
    /**
     * пользователь поменял статью, на проверку модератору
     */
    const PUBLISH_STATE_UPDATED = 5;
    /**
     * отложеная публикация
     */
    const PUBLISH_STATE_POSTPONE = 6;

    /**
     * Create new page without saving in database
     */
    public static function create()
    {
        $page = new Page();
        $page->status = self::PUBLISH_STATE_DRAFT;
        $page->site_id = \Bazalt\Site::getId();

        if (!\Bazalt\Auth::getUser()->isGuest()) {
            $page->user_id = \Bazalt\Auth::getUser()->id;
        }
        return $page;
    }

    /**
     * Get page by url
     */
    public static function getByUrl($url, $is_published = null, $userId = null)
    {
        $q = Page::select()
            ->where('url = ?', $url)
            ->andWhere('f.site_id = ?', \Bazalt\Site::getId());

        if ($is_published != null) {
            $q->andWhere('status >= ?', Page::PUBLISH_STATE_PUBLISHED);
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
            ->andWhere('is_published = ?', 1)
            ->groupBy('p.id');

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

    public static function getCollection($categoriesId = array())
    {
        $q = ORM::select('Components\Pages\Model\Page f', 'f.*')
            ->leftJoin('Components\Pages\Model\PageLocale ref', array('id', 'f.id'))
           // ->where('ref.lang_id = ?', CMS\Language::getCurrentLanguage()->id)
            ->andWhere('f.site_id = ?', \Bazalt\Site::getId());

        if (count($categoriesId) == 1) {
            $category = Category::getById((int)$categoriesId[0]);
            $childsQuery = ORM::select('Components\Pages\Model\Category c', 'id')
                ->where('c.lft BETWEEN ? AND ?', array($category->lft, $category->rgt))
                ->andWhere('c.site_id = ?', $category->site_id);

            $q->andWhereIn('f.category_id', $childsQuery);
        } else if (count($categoriesId) > 1) {
            $q->andWhereIn('f.category_id', $categoriesId);
        }
        $q->andWhere('status != ?', Page::PUBLISH_STATE_DELETED)
          ->orderBy('created_at DESC')
          ->groupBy('f.id');
        return new \Bazalt\ORM\Collection($q);
    }

    public static function getStatistic($start, $end)
    {
        $siteId = \Bazalt\Site::getId();

        $start = date('Y-m-d 00:00:00', $start);
        $end = date('Y-m-d H:i:s', $end);
        $q = ORM::select('Components\\Pages\\Model\\Page p', 'COUNT(*) as cnt, MAX(created_at) AS created_at, user_id')
            ->where('p.site_id = ?', (int)$siteId)
            ->andWhere('created_at BETWEEN ? AND ?', array($start, $end))
            ->groupBy('DAYOFMONTH(`created_at`), user_id')
            ->orderBy('`created_at`');

        return $q->fetchAll();
    }

    public function toArray()
    {
        $res = parent::toArray();

        unset($res['lang_id']);
        unset($res['completed']);
        unset($res['url']);

        $res['status'] = (int)$res['status'];
        $res['is_allow_comments'] = $res['is_allow_comments'] == '1';
        $res['is_highlight'] = $res['is_highlight'] == '1';
        $res['is_editor_choose'] = $res['is_editor_choose'] == '1';
        $res['own_photo'] = $res['own_photo'] == '1';
        $res['is_top'] = $res['is_top'] == '1';
        $res['rating'] = (int)$res['rating'];
        $res['region_id'] = (int)$res['region_id'];
        $res['url'] = '/post-' . $res['id'];

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
                $res['category'] = $data;
            }
        }

        $res['tags'] = [];
        $tags = $this->Tags->get();
        foreach ($tags as $tag) {
            $res['tags'][] = $tag->toArray();
        }

        $res['mainimage'] = null;

        $res['images'] = [];
        $images = $this->Images->get();
        foreach ($images as $image) {
            try {
                $res['images'][] = $image->toArray();
                if ($image->is_main) {
                    $res['mainimage'] = $image->toArray();
                }
            } catch (\Exception $e) {

            }
        }

        if (!$res['mainimage'] && count($res['images'])) {
            $res['mainimage'] = $res['images'][0];
        }

        $res['videos'] = [];
        $videos = $this->Videos->get();
        foreach ($videos as $video) {
            try {
                $res['videos'][] = $video->toArray();
                if (!$res['mainimage']) {
                    $res['mainimage'] = $video->toArray();
                    $res['mainimage']['is_video'] = 1;
                }
            } catch (\Exception $e) {

            }
        }
        return $res;
    }

    public function toIndex()
    {
        $res = parent::toArray();

        unset($res['lang_id']);
        unset($res['completed']);
        unset($res['url']);
        $res['title'] = $res['title']['en'];
        $res['body'] = $res['body']['en'];
        $res['status'] = (int)$res['status'];
        $res['hits'] = (int)$res['hits'];
        $res['is_allow_comments'] = $res['is_allow_comments'] == '1';
        $res['is_highlight'] = $res['is_highlight'] == '1';
        $res['is_editor_choose'] = $res['is_editor_choose'] == '1';
        $res['own_photo'] = $res['own_photo'] == '1';
        $res['is_top'] = $res['is_top'] == '1';
        $res['rating'] = (int)$res['rating'];
        $res['region_id'] = (int)$res['region_id'];
        $res['url'] = '/post-' . $res['id'];

        if ($user = $this->User) {
            $res['user'] = [
                'id' => $user->id,
                'name' => $user->getName(),
                'email' => $user->email
            ];
        }

        if ($category = $this->Category) {
            $res['breadcrumbs'] = [];
            $path = $this->Category->PublicElements->getPath();
            foreach ($path as $cat) {
                $data = $cat->toArray();
                unset($data['children']);
                if ($cat->id == 8) {
                    $res['is_blog'] = true;
                }
                $res['breadcrumbs'][] = $data;
            }
            if ($category->is_published && !$category->is_hidden) {
                $data = $category->toArray();
                unset($data['children']);
                $res['category'] = $data;
            }
        }

        $res['tags'] = [];
        $tags = $this->Tags->get();
        foreach ($tags as $tag) {
            $res['tags'][] = $tag->title;
        }

        $res['mainimage'] = null;

        $res['images'] = [];
        $images = $this->Images->get();
        foreach ($images as $image) {
            try {
                $res['images'][] = $image->toArray($this->own_photo);
                if ($image->is_main) {
                    $res['mainimage'] = $image->toArray($this->own_photo);
                }
            } catch (\Exception $e) {

            }
        }
        $res['has_photoreport'] = count($images) > 3;
        $res['has_video'] = preg_match("/(.*)vimeo\.com\/(.*)/", $this->body['en']) || preg_match("/(.*)youtobe\.com\/(.*)/", $this->body['en']);

        if (!$res['mainimage'] && count($res['images'])) {
            $res['mainimage'] = $res['images'][0];
        }

        /*$res['videos'] = [];
        $videos = $this->Videos->get();
        foreach ($videos as $video) {
            try {
                $res['videos'][] = $video->toArray();
                if (!$res['mainimage']) {
                    $res['mainimage'] = $video->toArray();
                    $res['mainimage']['is_video'] = 1;
                }
            } catch (\Exception $e) {

            }
        }*/
        return $res;
    }
}

/*
curl -XPOST "http://localhost:9200/news_vn_ua" -d '{
    "mappings" : {
    "com_pages_pages" : {
        "properties" : {
            "id": { "type": "integer" },
            "title": {"type": "string"},
            "body": {"type": "string", "include_in_all": false, "index": "no"},
            "url": {"type": "string"},
            "source": {"type": "string", "index": "not_analyzed"},
            "photo_source": {"type": "string", "index": "not_analyzed"},
            "publish_date": {"type": "date"},
            "status": { "type": "integer" },
            "own_photo": { "type": "boolean" },
            "is_allow_comments": { "type": "boolean" },
            "is_highlight": { "type": "boolean" },
            "is_editor_choose": { "type": "boolean" },
            "is_top": { "type": "boolean" },
            "hits": { "type": "integer" },
            "region_id": { "type": "integer" },
            "category_id": { "type": "integer" },
            "comments_count": { "type": "integer" },
            "rating": { "type": "integer" },
            "created_at": {"type": "date"},
            "updated_at": {"type": "date"},
            "user": {
                "properties" : {
                    "id" : {"type" : "integer"},
                    "name" : {"type" : "string"}
                }
            },
            "tags": { "type": "string", "index_name" : "tag", "analyzer": "keyword" }
        }
    }
}}'
*/