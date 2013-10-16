<?php

namespace Components\Pages\Model;

class Image extends Base\Image
{
    public static function create()
    {
        $image = new Image();
        return $image;
    }

    public function toArray()
    {
        $res = [
            'id' => $this->id,
            'name' => $this->name,
            'url' => 'http://' . \Bazalt\Site::get()->domain . $this->url,
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