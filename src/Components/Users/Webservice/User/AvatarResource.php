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

        $uploader = new \CMS\Uploader(['jpg', 'png', 'jpeg', 'bmp', 'gif'], 1000000);
        $file = $uploader->uploadTo('avatars');

        $result = [
            'thumbnailUrl' => thumb('/uploads' . $file, '200x200', ['crop' => true])
        ];
        $user->avatar = $file;
        $user->save();

        return new Response(Response::OK, $result);
    }
}