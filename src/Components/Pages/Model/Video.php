<?php

namespace Components\Pages\Model;

class Video extends Base\Video
{
    public static function create()
    {
        $video = new Video();
        return $video;
    }

    protected function video_image($url)
    {
        $image_url = parse_url($url);
        if ($image_url['host'] == 'www.youtube.com' || $image_url['host'] == 'youtube.com') {
            $array = explode("&", $image_url['query']);
            return "http://img.youtube.com/vi/" . substr($array[0], 2) . "/hqdefault.jpg";
        } else if ($image_url['host'] == 'www.youtu.be' || $image_url['host'] == 'youtu.be') {
            $array = explode("/", $image_url['path']);
            return "http://img.youtube.com/vi/" . $array[1] . "/hqdefault.jpg";
        } else if ($image_url['host'] == 'www.vimeo.com' || $image_url['host'] == 'vimeo.com') {
            $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/" . substr($image_url['path'], 1) . ".php"));
            return $hash[0]["thumbnail_small"];
        }
    }

    public function getImage()
    {
        if (!$this->image) {
            $this->image = $this->video_image($this->url);
            $fileName = md5($this->image);
            $path = '/uploads/video/' . $fileName{0} . $fileName{1} . '/' . $fileName{2} . $fileName{3};
            @mkdir(SITE_DIR . $path, 0777, true);
            $file = $path . '/' . $fileName . '.' . pathinfo($this->image, PATHINFO_EXTENSION);
            file_put_contents(SITE_DIR . $file, file_get_contents($this->image));
            $this->image = $file;
            $this->save();
        }
        return $this->image;
    }

    public function toArray()
    {
        $res = parent::toArray();

        $res['image_url'] = $this->getImage();
        $res['thumbnails'] = [
            'preview' => thumb($this->url, '160x100', ['fit' => true]),
            'main' => thumb($res['image_url'], '220x220', ['fit' => true, 'crop' => true]),
            'smallthumb' => thumb($res['image_url'], '107x107', ['fit' => true, 'crop' => true])
        ];

        return $res;
    }
}