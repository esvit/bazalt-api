<?php

namespace Components\Users\Webservice\User;
use Bazalt\Auth\Model\User;
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
        $user = User::getById((int)$id);
        if (!$user) {
            return new Response(Response::NOTFOUND, ['id' => 'User not found']);
        }

        $uploader = new \CMS\Uploader\Base(['jpg', 'png', 'jpeg', 'bmp', 'gif'], 1000000);
        $result = $uploader->handleUpload(SITE_DIR . '/uploads', '/uploads');

        $result['thumbnailUrl'] = thumb($result['url'], '200x200', ['crop' => true]);
        $user->avatar = $result['thumbnailUrl'];
        $user->save();

        return new Response(Response::OK, $result);
    }
}