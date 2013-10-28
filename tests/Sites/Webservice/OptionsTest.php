<?php

namespace tests\Sites\Webservice;

use Bazalt\Rest;
use Bazalt\Site;
use Tonic\Response;

class OptionsTest extends \tests\BaseCase
{
    protected $app;

    protected $site;

    protected function setUp()
    {
        parent::setUp();

        global $loader;

        $config = array(
            'load' => array(
                $loader->findFile('Components\\Sites\\Webservice\\OptionsResource'),
                $loader->findFile('Components\\Sites\\Webservice\\SiteResource'),
            )
        );
        $this->app = new \Tonic\Application($config);
    }

    public function testGetItem()
    {
        $this->user->is_god = true;
        $this->user->save();
        \Bazalt\Auth::setUser($this->user);

        \Bazalt\Site\Option::set('opt', 'testValue', $this->site->id);
        $data = [
            'opt' => 'testValue2'
        ];
        $response = new \Bazalt\Rest\Response(200, $data);
        $this->assertResponse('POST /sites/options/',
            [
                'data' => json_encode($data)
            ], $response);

        $response = new \Bazalt\Rest\Response(200,
            [
                'id' => $this->site->id,
                'domain' => '',
                'path' => '/',
                'languages' => 'en',
                'secret_key' => '',
                'theme_id' => '',
                'language_id' => 'en',
                'is_subdomain' => '',
                'is_active' => '',
                'is_allow_indexing' => '',
                'is_multilingual' => '',
                'user_id' => '',
                'site_id' => '',
                'is_redirect' => '',
                'lang_id' => '',
                'completed' => '',
                'title' => '',
                'created_at' => strtotime($this->site->created_at).'000',
                'updated_at' => strtotime($this->site->updated_at).'000',
                'options' => [
                    'opt' => 'testValue2'
                ]
            ]
        );
        $this->assertResponse('GET /sites/' . $this->site->id . '', [], $response);
    }
}