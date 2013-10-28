<?php

namespace Components\Sites\Webservice;
use Bazalt\Rest\Response,
    Bazalt\Site\Model\Option;

/**
 * OptionsResource
 *
 * @priority 100
 * @uri /sites/options
 */
class OptionsResource extends \Bazalt\Rest\Resource
{
    /**
     * @method POST
     * @json
     */
    public function saveItem()
    {
        if (!\Bazalt\Auth::getUser()->hasPermission('admin.access')) {
            return new Response(Response::FORBIDDEN, ['user' => 'Permission denied']);
        }

        foreach($this->request->data as $name => $value) {
            \Bazalt\Site\Option::set($name, $value);
        }
        return new Response(Response::OK, (array)$this->request->data);
    }
}