<?php

namespace Components\Files\Webservice;

/**
 * @uri /files
 */
class FilesResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @method POST
     * @json
     */
    public function getFiles()
    {
        $connector = \Components\Files\elFinder::connector();
        $connector->run();
        exit;
    }
}