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
                'preview' => thumb($this->url, '160x100', ['fit' => true])
            ]
        ];



        return $res;
    }
}