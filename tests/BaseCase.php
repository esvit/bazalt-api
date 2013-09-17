<?php

namespace tests;

use Tonic;
use Bazalt\Rest;

abstract class BaseCase extends \PHPUnit_Framework_TestCase
{
    protected $app;

    public function send($request, $options= [])
    {
        list($method, $uri) = explode(' ', $request);

        if (!is_array($options)) {
            $options = [];
        }
        if (!isset($options['contentType'])) {
            $options['contentType'] = 'application/json';
        }
        $options['method'] = $method;
        $options['uri'] = $uri;

        $request = new \Tonic\Request($options);

        $resource = $this->app->getResource($request);
        $response = $resource->exec();
        return [$response->code, json_decode($response->body, true)];
    }

    public function assertResponse($request, $options= [], \Bazalt\Rest\Response $assertResponse)
    {
        list($code, $response) = $this->send($request, $options);

        $this->assertEquals($assertResponse->body, $response);
        $this->assertEquals($assertResponse->code, $code);
    }
}