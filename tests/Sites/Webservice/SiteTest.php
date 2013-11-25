<?php

namespace tests\Sites\Webservice;

use Bazalt\Rest;
use Bazalt\Site;
use Tonic\Response;

class SiteTest extends \tests\BaseCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->initApp(getWebServices());
    }

    public function testGetItem()
    {
        \Bazalt\Site\Option::set('opt', 'testValue', $this->site->id);

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
                    'opt' => 'testValue'
                ]
            ]
        );
        $this->assertResponse('GET /sites/' . $this->site->id . '', [], $response);
    }
}