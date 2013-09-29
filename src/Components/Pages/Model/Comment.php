<?php

namespace Components\Pages\Model;

class Comment extends Base\Comment
{
    /**
     * Коментар не промодерований
     */
    const COMMENT_TYPE_NOMODERATED  = 0;

    /**
     * Коментар промодерований
     */
    const COMMENT_TYPE_MODERATED    = 1;

    /**
     * Коментар заборонено для відображення
     */
    const COMMENT_TYPE_BANNED       = 2;

    /**
     * Повертає типи модерацій коментарів
     *
     * @return array
     */
    public static function getModerationTypes()
    {
        return array( 
            self::COMMENT_TYPE_NOMODERATED  => 'No moderated',
            self::COMMENT_TYPE_MODERATED    => 'Moderated',
            self::COMMENT_TYPE_BANNED       => 'Banned'
        );
    }

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

    public static function getRoot(Page $page)
    {
        $q = Comment::select()
            ->where('depth = ?', 0)
            ->andWhere('page_id = ?', $page->id);

        $root = $q->fetch();
        if (!$root) {
            $root = Comment::create($page);
            $root->save();
        }
        return $root;
    }

    public function getUrl($withHost = false)
    {
        return $this->Article->getUrl($withHost) . '#comment' . $this->id;
    }

    public static function create(Page $page)
    {
        $comment = new Comment();
        $comment->page_id = $page->id;
        if (!\Bazalt\Auth::getUser()->isGuest()) {
            $comment->user_id = \Bazalt\Auth::getUser()->id;
        }
        $comment->lft = 1;
        $comment->rgt = 2;
        $comment->is_moderated = 0;
        return $comment;
    }

    public static function getLatestComments($limit = 5)
    {
        $q = ORM::select('ComNewsChannel_Model_Comment c')
            ->andWhere('ip != 0')
            ->andWhere('is_deleted = 0')
            ->andWhere('is_moderated = 1')
            ->orderBy('created_at DESC')
            ->limit($limit);

        return $q->fetchAll();
    }

    public static function getCommentsCollection($moderated = false)
    {
        $q = ComNewsChannel_Model_Comment::select()
                ->andWhere('ip != 0')
                ->orderBy('id DESC');

        if ($moderated) {
            $q->andWhere('is_moderated = ?', 1);
        }
        return new CMS_ORM_Collection($q);
    }

    public static function getCommentsForItem(Page $item)
    {
        $q = \Bazalt\ORM::select('Components\\Pages\\Model\\Comment c')
                ->where('page_id = ?', (int)$item->id)
                ->andWhere('depth > 0')
                ->orderBy('lft ASC');

        return $q->fetchAll();
    }

    /**
     * @param $itemId
     * @param $commentId
     * @param int $maxDepth
     * @return ComNewsChannel_Model_Comment
     */
    public static function getComment($itemId, $commentId = -1)
    {
        if ($commentId != -1) {
            $q = ORM::select('ComNewsChannel_Model_Comment c')
                    ->where('c.news_id = ?', $itemId)
                    ->andWhere('c.id = ?', $commentId);

            return $q->fetch();
        }
        $q = ORM::select('ComNewsChannel_Model_Comment c')
                ->where('c.news_id = ?', $itemId)
                ->andWhere('c.depth = ?', 0);

        return $q->fetch();
    }

    public static function getCountFromDate($date = null)
    {
        $q = ORM::select('ComNewsChannel_Model_Comment o')
                ->andWhere('ip != 0');
        if ($date != null) {
            $q->where('o.created_at > FROM_UNIXTIME(?)', $date);
        }
        return $q->exec();
    }

    public function toArrayForPage($params = [])
    {
        return array(
            'id'            => $this->id,
            'nickname'     => $this->nickname,
            'body'          => $this->body,
            //'news_id'          => $this->news_id,
            //'news_title'          => $this->Article->title,
            //'avatar'        => $this->getAvatar(),
            'created_at'    => strToTime($this->created_at)
            //'is_deleted'    => $this->is_deleted
        );
    }

    public function toArray($params = [])
    {
        $res = array(
            'id'            => (int)$this->id,
            'nickname'      => $this->nickname,
            'body'          => $this->body,
            'depth'         => (int)$this->depth,
            'rating'        => (int)$this->rating,
            'created_at'    => strToTime($this->created_at) . '000'
        );
        if ($this->user_id) {
            $res['nickname'] = $this->User->getName();
        }
        if ($this->user_id && ($avatar = $this->User->avatar)) {
            $res['avatar'] = thumb($avatar, '24x24', ['crop' => true]);
        }
        if (isset($this->Childrens) && count($this->Childrens)) {
            $res['children'] = [];
            foreach ($this->Childrens as $child) {
                $res['children'] []= $child->toArray();
            }
        }
        return $res;
    }
}
