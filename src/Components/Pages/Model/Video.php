<?php

namespace Components\Pages\Model;

class Video extends Base\Video
{
    public static function create()
    {
        $video = new Video();
        return $video;
    }
}