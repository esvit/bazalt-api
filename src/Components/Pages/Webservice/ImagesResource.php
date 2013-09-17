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
        $uploader = new \CMS\Uploader\Base(['jpg', 'png', 'jpeg', 'bmp', 'gif'], 1000000);
        $result = $uploader->handleUpload(SITE_DIR . '/uploads', '/uploads');

        return new Response(Response::OK, $result);
    }
}