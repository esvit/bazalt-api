<?php

namespace Components\Pages\Webservice;
use Bazalt\Rest\Response;
use Components\Pages\Model\Page;

/**
 * ImagesResource
 *
 * @uri /pages/images
 * @uri /pages/:id/images
 */
class ImagesResource extends \Bazalt\Rest\Resource
{
    /**
     * @method POST
     * @accepts multipart/form-data
     * @json
     */
    public function uploadPoster()
    {
        $uploader = new \CMS\Uploader(['jpg', 'png', 'jpeg', 'bmp', 'gif'], 1000000);
        $file = $uploader->uploadTo('pages');

        $result = [
            'url' => '/uploads' . $file,
            'thumbnailUrl' => thumb('/uploads' . $file, '80x80')
        ];
        return new Response(Response::OK, $result);
    }

    /**
     * @method POST
     * @accepts application/json
     * @json
     */
    public function saveDataUrl()
    {
        $image = $this->request->data->data;
        $image = substr($image, strpos($image, ",")+1);
        $image = base64_decode($image);

        $fp = fopen(UPLOAD_DIR .  "/img_".microtime(1).".png", 'w');
        fwrite($fp, $image);
        fclose($fp);

        return new Response(Response::OK, "/img_".microtime(1).".png");
    }
}