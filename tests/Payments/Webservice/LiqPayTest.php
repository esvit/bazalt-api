<?php

namespace tests\Payments\Webservice;

use Bazalt\Rest;
use Components\Pages\Model\Comment;
use Components\Pages\Model\Page;
use Tonic\Response;

class LiqPayTest extends \tests\BaseCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->initApp(getWebServices());
    }

    public function testGet()
    {
        $response = new \Bazalt\Rest\Response(400, ['amount' => 'Invalid value']);
        $this->assertResponse('GET /payments/transaction', [], $response);

       /* $xml = '#<request>
				<version>1.2</version>
				<merchant_id>i1387024747</merchant_id>
				<result_url>(.*)</result_url>
				<server_url>(.*)</server_url>
				<order_id>(.*)</order_id>
				<amount>10</amount>
				<default_phone></default_phone>
				<currency>UAH</currency>
				<description>Test</description>
				<pay_way>card</pay_way>
 				</request>#i';

        list($code, $response) = $this->send('GET /payments/transaction', ['data' => ['amount' => 10]]);
        $this->assertEquals($code, 200);
        $this->assertRegExp($xml, base64_decode($response['xml']));*/
    }
}