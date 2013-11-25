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

    public function toArray()
    {
        $res = parent::toArray();

        $res['image_url'] = $this->video_image($this->url);
        $res['thumbnails'] = [
            'main' => thumb($this->url, '220x220', ['fit' => true, 'crop' => true])
        ];

        return $res;
    }
}