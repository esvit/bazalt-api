<?php

namespace Components\Events\Model;

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
            self::COMMENT_TYPE_NOMODERATED  => __('No moderated', ComNewsChannel::getName()),
            self::COMMENT_TYPE_MODERATED    => __('Moderated', ComNewsChannel::getName()),
            self::COMMENT_TYPE_BANNED       => __('Banned', ComNewsChannel::getName())
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

    public static function getRoot($articleId)
    {
        $q = ORM::select('ComNewsChannel_Model_Comment')
            ->where('depth = ?', 0)
            ->andWhere('news_id = ?', $articleId);

        $root = $q->fetch();
        return $root;
    }

    public function getUrl($withHost = false)
    {
        return $this->Article->getUrl($withHost) . '#comment' . $this->id;
    }

    public static function create(Article $article)
    {
        $comment = new Comment();
        $comment->news_id = $article->id;
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

    public static function getCommentsForItem($itemId, $moderated = false)
    {
        $q = ORM::select('ComNewsChannel_Model_Comment c')
                ->where('news_id = ?', (int)$itemId)
                ->andWhere('depth > 0')
                ->orderBy('lft ASC');

        if ($moderated) {
            $q->andWhere('c.is_moderated = ?', 1)
              ->andWhere('c.is_deleted = ?', 0);
        }
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

    public function getAvatar()
    {
        if ($this->user_id == null) {
            return ComTracking::getAvatar(48, $this->ip, $this->browser_id);
        }
        return $this->User->getAvatar('[48x48]');
    }

    public function toArray($params = [])
    {
        return array(
            'id'            => $this->id,
            'user_name'     => $this->user_name,
            'body'          => $this->body,
            //'avatar'        => $this->getAvatar(),
            'created_at'    => strToTime($this->created_at) . '000'
            //'is_deleted'    => $this->is_deleted
        );
    }
}
