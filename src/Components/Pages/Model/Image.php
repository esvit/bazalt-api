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
            'is_main' => (int)$this->is_main,
            'url' => $config['uploads.prefix'] . $this->url,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnailUrl' => thumb($this->url, '100x100', ['crop' => true, 'fit' => true]),
            'size' => (double)$this->size,
            'thumbnails' => [
                /*'preview' => thumb($this->url, '160x100', ['fit' => true]),
                'main' => thumb($this->url, '220x220', ['fit' => true, 'crop' => true]),
                'person' => thumb($this->url, '460x450', ['fit' => true, 'crop' => true]),
                'smallthumb' => thumb($this->url, '107x107', ['fit' => true, 'crop' => true]),
                'face' => thumb($this->url, '100x100', ['fit' => true, 'crop' => true])*/
                'large' => thumb($this->url, '460x290', ['fit' => true, 'crop' => true]),
                'medium' => thumb($this->url, '350x230', ['fit' => true, 'crop' => true]),
                'interview' => thumb($this->url, '100x80', ['fit' => true, 'crop' => true]),
                'small' => thumb($this->url, '80x60', ['fit' => true, 'crop' => true])
            ]
        ];



        return $res;
    }
}