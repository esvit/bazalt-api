<?php

namespace CMS;

class Uploader extends \Bazalt\Rest\Uploader
{
    public function uploadTo($type)
    {
        $siteId = \Bazalt\Site::getId();
        $file = $this->handleUpload($siteId, [$type]);

        /*$result = [];
        $json = file_get_contents(SITE_DIR . '/thumbnails.json');
        $thumbnails = json_decode($json, true);
        print_R($thumbnails);exit;*/
        return $file;
    }
}