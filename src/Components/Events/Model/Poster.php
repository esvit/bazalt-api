<?php

namespace Components\Events\Model;

class Poster extends Base\Poster
{
    /**
     * Default article
     */
    const DEFAULT_ARTICLE = 0;

    /**
     * Default article
     */
    const TOP_EXPIRATION_DAYS_COUNT = 1;

    /**
     * Article with photos
     */
    const PHOTOREPORT_ARTICLE = 1;

    /**
     * Article with video
     */
    const VIDEO_ARTICLE = 2;

    public function getDate($format)
    {
        $timestamp = strtotime($this->created_at);
        if ($format == 'atom') {
            $date = strftime('%Y-%m-%dT%H:%M:%S', $timestamp);
            $date .= date('P', ($timestamp != null) ? $timestamp : time());
            return $date;
        }
        return strftime($format, ($timestamp != null) ? $timestamp : time());
    }

    public function toArray()
    {
        $res = parent::toArray();
        $res['is_published'] = $res['is_published'] == '1';
        $res['url'] = $this->getUrl();
        $res['start_date'] = strToTime($this->start_date) . '000';
        $res['end_date'] = strToTime($this->end_date) . '000';

        try {
            if ($this->image) {
                $res['thumb'] = thumb($this->image, '200x300', ['crop' => true]);
            }
        } catch (\Exception $e) {

        }

        $res['comments'] = [];
        foreach ($this->Comments->get() as $comment) {
            if ($comment->depth > 0) {
                $res['comments'][] = $comment->toArray();
            }
        }
        return $res;
    }

    public static function create()
    {
        $poster = new Poster();

        $user = \Bazalt\Auth::getUser();
        if (!$user->isGuest()) {
            $poster->user_id = $user->id;
        }
        return $poster;
    }

    public static function getRegions()
    {
        $q = ORM::select('ComGeo_Model_State s', 's.*, COUNT(*) AS cnt')
                ->leftJoin('\Components\News\Model\Article a', array('region_id', 's.id'))
                ->where('s.type = "RegionalCenter"')//region_id IS NOT NULL')
                ->groupBy('s.id')
                ->orderBy('cnt DESC');
        $results = $q->fetchAll();

        $region = ComGeo_Model_State::getByAlias('ukraine');
        $result = array(
            'world' => __('World', ComNewsChannel::getName()),
            $region->id => __('Ukraine', ComNewsChannel::getName())
        );
        foreach ($results as $state) {
            $result[$state->id] = $state->title;
        }
        return $result;
    }

    public static function getVideoBySource($source, $siteId = null)
    {
        if (!$siteId) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::select('Components\News\Model\Article n')
            ->where('n.source = ?', $source)
            ->andWhere('n.site_id = ?', (int)$siteId);

        return $q->fetch();
    }

    public static function getVideoNews($published = null, $siteId = null)
    {
        if (!$siteId) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::select('Components\News\Model\Article n')
                ->innerJoin('Components\News\Model\ArticleLocale nl', array('id', 'n.id'))
                ->where('nl.body LIKE ?', '%youtube%')
                ->andWhere('n.site_id = ?', (int)$siteId)
                ->orderBy('created_at DESC');

        if ($published) {
            $q->andWhere('publish = ?', 1);
        }
        return new CMS\ORM\Collection($q);
    }

    public function getVideoThumb()
    {
        using('Framework.System.Google');
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^>"&?/ ]{11})%i', $this->body, $match)) {
            return Google_Youtube::getScreen($match[1], 'big');
        }
    }

    public static function getByIdAndSiteId($id, $siteId = null)
    {
        if (!$siteId) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::select('Components\News\Model\Article p')
            ->where('p.id = ?', (int)$id)
            ->andWhere('p.site_id = ?', (int)$siteId);

        return $q->fetch();
    }

    public static function getByIdAndCompanyId($id, $companyId)
    {
        $q = ORM::select('\Components\News\Model\Article p')
            ->where('p.id = ?', (int)$id)
            ->andWhere('p.company_id = ?', (int)$companyId);

        return $q->fetch();
    }

    public static function getStatistic($start, $end)
    {
        $siteId = CMS\Bazalt::getSiteId();

        $start = date('Y-m-d 00:00:00', $start);
        $end = date('Y-m-d H:i:s', $end);
        $q = ORM::select('\Components\News\Model\Article p', 'COUNT(*) as cnt, MAX(created_at) AS created_at, user_id')
                ->where('p.site_id = ?', (int)$siteId)
                ->andWhere('created_at BETWEEN ? AND ?', array($start, $end))
                ->groupBy('DAYOFMONTH(`created_at`), user_id')
                ->orderBy('`created_at`');

        return $q->fetchAll();
    }

    public static function deleteByIds($ids)
    {
        if(!is_array($ids)) {
            $ids = array($ids);
        }
        $q = ORM::delete('\Components\News\Model\Article a')
                ->whereIn('a.id', $ids)
                ->andWhere('a.site_id = ?', CMS\Bazalt::getSiteId());

        return $q->exec();
    }

    public static function getByIdAndCategory($id, $category = null)
    {
        $q = ORM::select('\Components\News\Model\Article n')
                ->where('n.id = ?', $id)
                ->andWhere('n.site_id = ?', CMS\Bazalt::getSiteId());

        if ($category != null) {
            $q->andWhere('n.category_id = ?', $category->id);
        }
        return $q->fetch();
    }

    public function getByUrlAndCategory($url, $category = null)
    {
        $q = ORM::select('\Components\News\Model\Article n')
                ->where('n.url = ?', $url)
                ->andWhere('n.site_id = ?', CMS\Bazalt::getSiteId());

        if ($category != null) {
            $q->andWhere('n.category_id = ?', $category->id);
        }
        return $q->fetch();
    }

    public function getUrl($withHost = false)
    {
        return 'http://'.$_SERVER['HTTP_HOST'].'/news/'. $this->id;
        return Route::urlFor('News.Article.Region.Category', array('region' => $this->Region, 'category' => $this->Category, 'id' => $this->id), $withHost);
    }

    public function getRelatedNews($category, $limit = 5)
    {
        $tags = $this->Tags->get();
        if (count($tags) == 0) {
            return array();
        }

        $ids = array();
        foreach ($tags as $tag) {
            $ids []= $tag->id;
        }

        $q = ORM::select('\Components\News\Model\ArticleRefTag nt', 'n.*, COUNT(*) AS related_tags_count')
            ->leftJoin('\Components\News\Model\Article n', array('id', 'nt.news_id'))
            ->whereIn('nt.tag_id', $ids)
            ->andWhere('nt.news_id != ?', $this->id)
            ->andWhere('n.site_id = ?', CMS\Bazalt::getSiteId())
            ->andWhere('n.publish = ?', 1)
            ->groupBy('nt.news_id')
            ->orderBy('related_tags_count DESC, n.created_at DESC')
            ->limit($limit);

        if ($category != null) {
            $q->andWhere('n.category_id = ?', $category->id);
        }

        return $q->fetchAll('\Components\News\Model\Article');
    }

    public function getRelatedNewsWithExclude($category, $limit = 5)
    {
        $q = ORM::select('\Components\News\Model\ArticleRefTag nt', 'n.*, COUNT(*) AS related_tags_count')
            ->leftJoin('\Components\News\Model\Article n', array('id', 'nt.news_id'))
            ->whereIn('nt.tag_id', array(19, 27))
            ->andWhere('nt.news_id != ?', $this->id)
            ->andWhere('n.site_id = ?', CMS\Bazalt::getSiteId())
            ->andWhere('n.publish = ?', 1)
            ->groupBy('nt.news_id')
            ->orderBy('related_tags_count DESC, n.created_at DESC')
            ->limit($limit);

        if ($category != null) {
            $q->andWhere('n.category_id != ?', $category->id);
        }
        return $q->fetchAll('\Components\News\Model\Article');
    }

    public static function getCollection($published = false, $category = null, $region = null, $siteId = null, $userId = null)
    {
        if(!$siteId) {
            $siteId = 6;//CMS\Bazalt::getSiteId();
        }
        $q = \Bazalt\ORM::select('Events\Model\Poster n')
            //->andWhere('n.site_id = ?', $siteId)
            ->orderBy('created_at DESC')
            ->groupBy('n.id');

        if ($category) {
            $childsQuery = \Bazalt\ORM::select('News\Model\Category c', 'id')
                ->where('c.lft BETWEEN ? AND ?', array($category->lft, $category->rgt))
                ->andWhere('c.site_id = ?', $category->site_id);
            $q->andWhereIn('n.category_id', $childsQuery);
        }
        if ($region && $region->id) {
            $q->andWhere('n.region_id = ?', $region->id);
        } else if ($region && !$region->id) {
            $q->andWhere('n.region_id IS NULL');
        }
        if ($published) {
            $q->andWhere('n.publish = ?', 1);
        }
        if ($userId) {
            $q->andWhere('user_id = ?', $userId);
        }
        return new \Bazalt\ORM\Collection($q);
    }

    public static function getLatestNewsCollection($published = false, $limit = 10)
    {
        $collection = self::getCollection()
            ->limit($limit)
            ->orderBy('created_at DESC');
        if ($published) {
            $collection->andWhere('publish = ?', 1);
        }
        return $collection;
    }

    public static function getPhotoReports($published = null)
    {
        $q = ORM::select('Components\News\Model\Article n')
            ->where('n.item_type = ?', self::PHOTOREPORT_ARTICLE)
            ->orderBy('created_at DESC')
            ->andWhere('n.site_id = ?', CMS\Bazalt::getSiteId());

        if ($published) {
            $q->andWhere('publish = ?', 1);
        }
        return new CMS\ORM\Collection($q);
    }

    public static function getCollectionByCategory($category, $published = false)
    {
        $childsQuery = ORM::select('Components\News\Model\Category c', 'id')
            ->where('c.lft BETWEEN ? AND ?', array($category->lft, $category->rgt))
            ->andWhere('c.site_id = ?', $category->site_id);

        $q = ORM::select('Components\News\Model\Article n', 'n.*')
        //->innerJoin('\Components\News\Model\ArticleLocale ref', array('id', 'f.id'))
        //    ->innerJoin('\Components\News\Model\ArticleRefCategory c', array('news_id', 'n.id'))
        //->where('ref.lang_id = ?', CMS_Language::getCurrentLanguage()->id)
            ->andWhereIn('n.category_id', $childsQuery)
            ->andWhere('n.site_id = ?', CMS\Bazalt::getSiteId())
            ->orderBy('created_at DESC')
            ->groupBy('n.id');

        if ($published) {
            $q->andWhere('n.publish = ?', 1);
        }
        return new CMS\ORM\Collection($q);
    }

    public static function getCollectionByCompany($company, $published = false)
    {
        $q = ORM::select('\Components\News\Model\Article n', 'n.*')
        //->innerJoin('\Components\News\Model\ArticleLocale ref', array('id', 'f.id'))
        //    ->innerJoin('\Components\News\Model\ArticleRefCategory c', array('news_id', 'n.id'))
        //->where('ref.lang_id = ?', CMS_Language::getCurrentLanguage()->id)
            ->andWhere('n.company_id = ?', $company->id)
            ->orderBy('created_at DESC')
            ->groupBy('n.id');

        if ($published) {
            $q->andWhere('n.publish = ?', 1);
        }
        return new CMS_ORM_Collection($q);
    }

    public static function getCollectionByTag($tag, $published = false)
    {
        $q = ORM::select('\Components\News\Model\Article n', 'n.*')
            ->innerJoin('\Components\News\Model\ArticleRefTag t', array('news_id', 'n.id'))
            ->andWhere('t.tag_id = ?', $tag->id)
            ->andWhere('n.site_id = ?', CMS\Bazalt::getSiteId())
            ->orderBy('created_at DESC')
            ->groupBy('n.id');

        if ($published) {
            $q->andWhere('n.publish = ?', 1);
        }
        return new CMS_ORM_Collection($q);
    }

    public static function getRegionId($val)
    {
        switch($val) {
            case 'none':
            case 'world':
                return null;
                break;
            case 'ukraine':
                return ComGeo_Model_State::getByAlias('ukraine');
                break;
            default:
                return ComGeo_Model_State::getById((int)$val);
        }
    }

    public static function getTopNews($days = 5, $category = null, $region = null, $published = false, $excludeRegion = false)
    {
        $regionObject = self::getRegionId($region);
        if ($category) {
            $collection = self::getCollectionByCategory($category, $published);
        } else {
            $collection = self::getLatestNewsCollection($published);
        }
        if ($region != 'none') {
            if ($region == 'world') {
                $collection->andWhere('n.region_id IS NULL');
            } else if ($regionObject) {
                if ($excludeRegion) {
                    $collection->andWhere('n.region_id != ?', $regionObject->id)
                               ->andWhere('n.region_id IS NOT NULL');

                } else {
                    $collection->andWhere('n.region_id = ?', $regionObject->id);
                }
            }
        }
        $collection->select('n.*, n.is_top && (DATEDIFF(NOW(), n.created_at) < ' . (int)$days . ') AS in_top')
            ->orderBy('in_top DESC, n.created_at DESC')
            ->groupBy('n.id');

        return $collection;
    }

    public function tagsSave($tags)
    {
        $tagsNew = array();
        $tagsOld = array();

        foreach ($this->Tags->get()as $tagObj) {
            $tagsOld[$tagObj->id] = $tagObj;
        }

        foreach ($tags as $tagName) {
            $tagName = trim($tagName);
            if ($tagName != '') {
                $tag = ComTags_Model_Tag::getByName($tagName);
                if (!$tag) {
                    $tag = ComTags_Model_Tag::create($tagName);
                }

                if (!$this->Tags->has($tag)) {
                    $this->Tags->add($tag);
                    $tag->count++;
                    $tag->save();
                }
                $tagsNew[$tag->id] = $tag;
            }
        }

        foreach ($tagsOld as $id=>$tag) {
            if (!isset($tagsNew[$id])) {
                $this->Tags->remove($tag);
                $tag->count--;
                if ($tag->count == 0) {
                    $tag->hit = 0;
                }
                $tag->save();
            }
        }
    }

    protected function processImage($matches)
    {
        $width = null;
        $height = null;

        $matches['attr'] = $matches['attr1'] . ' ' . $matches['attr2'];

        $string = '/(.*?)data-processed-preview="true"(.*?)/';
        preg_match_all($string, $matches['attr'], $matches2, PREG_SET_ORDER);
        if (count($matches2) > 0) {
            return $matches[0];
        }

        $string = '/(.*?)width="(?P<width>[0-9]*?)"(.*?)/';
        preg_match_all($string, $matches['attr'], $matches2, PREG_SET_ORDER);

        if (isset($matches2[0]['width'])) {
            $width = (int)$matches2[0]['width'];
        }

        $string = '/(.*?)height="(?P<height>[0-9]*?)"(.*?)/';
        preg_match_all($string, $matches['attr'], $matches2, PREG_SET_ORDER);
        if (isset($matches2[0]['height'])) {
            $height = (int)$matches2[0]['height'];
        }

        $size = null;
        if ($width && $height) {
            $size = $width . 'x' . $height;
        } else if ($width) {
            $size = $width . 'x0';
        } else if ($height) {
            $size = '0x' . $height;
        } else {
            return $matches[0];
        }
        try {
            if (substr($matches['img'], 0, 5) == 'http:') {
                $matches['img'] = relativePath(Assets_FileManager::copy($matches['img'], UPLOAD_DIR));
            }
        } catch (\Exception $e) {
            return $matches[0];
        }
        $img = thumb($matches['img'], $size);
        if (!$img) {
            return $matches[0];
        }

        return '<a class="preview" href="' . $matches['img'] . '">' .
               '<img ' . $matches['attr1'] . ' data-processed-preview="true" src="' . $img . '" ' . $matches['attr2'] . ' />' .
               '</a>';
    }

    public function replaceImages()
    {
        $string = '/<img(?P<attr1>.*?)src="(?P<img>[^"]*?)"(?P<attr2>.*?)\/>/i';
        $this->body = preg_replace_callback($string, array($this, 'processImage'), $this->body);
    }

    public static function getAuthors($siteId = null)
    {
        if (!$siteId) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::select('Framework\CMS\Model\User u', 'u.*')
            ->innerJoin('Components\News\Model\Article a', array('user_id', 'u.id'))
            ->andWhere('a.site_id = ?', $siteId)
            ->groupBy('u.id');

        return $q->fetchAll();
    }

    public function save()
    {
        /*if (empty($this->url)) {
            $this->url = Url::cleanUrl($this->title);
        }
        if (!$this->is_top) {
            $this->is_top = 0;
        }
        if (!$this->publish) {
            $this->publish = 0;
        }*/

        parent::save();
    }
}