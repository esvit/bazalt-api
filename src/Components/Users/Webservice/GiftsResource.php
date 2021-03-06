<?php

namespace Components\Users\Webservice;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Components\Users\Model\Gift;
use Bazalt\Rest\Response;

/**
 * UsersResource
 *
 * @uri /auth/users/gifts
 * @priority 100
 */
class GiftsResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getList()
    {
        $collection = Gift::getCollection();
        if (!\Bazalt\Auth::getUser()->isGuest() && !\Bazalt\Auth::getUser()->is_god) {
            $collection->andWhere('is_published = ?', 1);
        }

        $table = new \Bazalt\Rest\Collection($collection);
        $table->sortableBy('price');

        return new Response(Response::OK, $table->fetch($_GET));
    }

    /**
     * @method POST
     * @accepts multipart/form-data
     * @json
     */
    public function uploadImage()
    {
        $uploader = new \CMS\Uploader\Base(['jpg', 'png', 'jpeg', 'bmp', 'gif'], 1000000);
        $result = $uploader->handleUpload(SITE_DIR . '/uploads', '/uploads');

        $result['thumbnailUrl'] = thumb($result['url'], '200x200', ['crop' => true]);

        return new Response(Response::OK, $result);
    }
}