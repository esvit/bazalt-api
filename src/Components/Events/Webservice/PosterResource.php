<?php

namespace Components\Events\Webservice;

/**
 * PosterResource
 *
 * @uri /events/posters/:id
 */
class PosterResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getPoster($id)
    {
        $poster = \Components\Events\Model\Poster::getById((int)$id);
        if (!$poster) {
            return new \Bazalt\Rest\Response(\Bazalt\Rest\Response::NOTFOUND, '404');
        }
        return new \Bazalt\Rest\Response(\Bazalt\Rest\Response::OK, $poster->toArray());
    }

    /**
     * @method POST
     * @accepts application/json
     * @json
     */
    public function savePoster($id = null)
    {
        $data = \Bazalt\Data\Validator::create($this->request->data);
        $poster = ($id != null) ? \Components\Events\Model\Poster::getById((int)$id) : \Components\Events\Model\Poster::create();

        if (!$poster) {
            return new \Tonic\Response(\Tonic\Response::NOTFOUND, '404');
        }
        $data->field('title')->required();
        $data->field('start_date')->required();
        $data->field('end_date')->required();
        $data->field('image')->required();
        $data->field('type')->required();
        if (!$data->validate()) {
            return new \Bazalt\Rest\Response(400, $data->errors());
        }
        $poster->title = $data['title'];
        $poster->is_published = $data['is_published'] == 'true';
        $poster->start_date = date('Y-m-d H:i:s', substr($data['start_date'], 0, -3));
        $poster->end_date = date('Y-m-d H:i:s', substr($data['end_date'], 0, -3));
        $poster->image = isset($data['image']) ? $data['image'] : null;
        $poster->type = $data['type'];
        $poster->save();

        $poster->save();

        return new \Bazalt\Rest\Response(\Bazalt\Rest\Response::OK, $poster->toArray());
    }

    public static function getRemoteIp()
    {
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ?
            (isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['HTTP_X_FORWARDED_FOR']) :
            (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1');
        //localhost for CLI mode

        return $ip;
    }

    public static function getUserAgent()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        }
        return 'unknown';
    }
}
