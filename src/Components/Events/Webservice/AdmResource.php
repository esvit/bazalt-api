<?php

namespace Components\Events\Webservice;
use Bazalt\Rest\Response;
use Components\Events\Model\Adm;


/**
 * PostersResource
 *
 * @uri /adm
 */
class AdmResource extends \Bazalt\Rest\Resource
{
    /**
     * @method PUT
     * @json
     */
    public function saveSanta()
    {
        $dataValidator = \Bazalt\Site\Data\Validator::create($this->request->data);
        $santa = Adm::create();

        $dataValidator->field('email')->required();
        $dataValidator->field('phone')->required();
        $dataValidator->field('address')->required();

        if (!$dataValidator->validate()) {
            return new Response(Response::BADREQUEST, $dataValidator->errors());
        }

        $santa->email = $dataValidator['email'];
        $santa->phone = $dataValidator['phone'];
        $santa->address = $dataValidator['address'];
        $santa->letter = $dataValidator['letter'];
        $santa->ip = $this->getRemoteIp();
        $santa->browser = $this->getUserAgent();

        $santa->save();

        return new Response(Response::OK, $santa->toArray());
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
