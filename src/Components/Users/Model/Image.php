<?php

namespace Components\Users\Model;

class Image extends Base\Image
{
    public static function create()
    {
        $image = new Image();
        return $image;
    }

    public static function getUserImages($userId)
    {
        $q = Image::select()->where('user_id = ?', $userId);
        return $q->fetchAll();
    }

    public static function clean($ids)
    {
        $q = ORM::delete('Components\\Users\\Model\\Image')->whereIn('id', $ids);
        return $q->exec();
    }
    public function toArray()
    {
        $config = \Bazalt\Config::container();
        $res = [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $config['uploads.prefix'] . $this->url,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnailUrl' => thumb($this->url, '80x80'),
            'size' => (double)$this->size,
            'thumbnails' => [
                'preview' => thumb($this->url, '160x100', ['fit' => true, 'crop' => true])
            ]
        ];
        return $res;
    }
}