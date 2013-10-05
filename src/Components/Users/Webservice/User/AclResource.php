<?php

namespace Components\Users\Webservice\User;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Tonic\Response;

/**
 * RoleResource
 *
 * @uri /auth/acl
 */
class AclResource extends \Tonic\Resource
{
    /**
     * Condition method to turn output into JSON
     */
    protected function json()
    {
        $this->before(function ($request) {
            if ($request->contentType == "application/json") {
                $request->data = json_decode($request->data);
            }
        });
        $this->after(function ($response) {
            $response->contentType = "application/json";

            if (isset($_GET['jsonp'])) {
                $response->body = $_GET['jsonp'].'('.json_encode($response->body).');';
            } else {
                $response->body = json_encode($response->body);
            }
        });
    }

    /**
     * @method GET
     * @provides text/javascript
     */
    public function getAcl()
    {
        $result = \Bazalt\Auth::getAclLevels();
        $result = json_encode($result);
        $result = 'define([],function(){return ' . $result . '})';
        $response = new Response(Response::OK, $result);
        $response->contentType = "application/json";
        return $response;
    }
}