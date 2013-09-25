<?php

namespace Components\Users\Webservice\User;
use Bazalt\Rest\Response;

/**
 * AvatarResource
 *
 * @uri /users/:id/avatar
 */
class AvatarResource extends \Bazalt\Rest\Resource
{
    /**
     * @method POST
     * @accepts multipart/form-data
     * @json
     */
    public function uploadAvatar($id)
    {
        $uploader = new \CMS\Uploader\Base(['jpg', 'png', 'jpeg', 'bmp', 'gif'], 1000000);
        $result = $uploader->handleUpload(SITE_DIR . '/uploads', '/uploads');

        return new Response(Response::OK, $result);
    }
}