<?php

namespace tests\helpers;

use Bazalt\Rest;
use Bazalt\Site;
use Tonic\Response;

class truncateTest extends \tests\BaseCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testTruncate()
    {
        $str = '1 2 3 4 5 6 7';
        $this->assertEquals('1 2 3...', truncate($str, 10));

        $str = "Мої думки з розпеченої сталі\nСтікали краплями солодкого сиропу.\nВсі брали пробу.\n\nМої слова із пережатим горлом\nХрипіли втомлено у темряву небесну.\nБуло чудесно.";
        $this->assertEquals("Мої думки з розпеченої сталі\nСтікали краплями...", truncate($str, 100));

        $str = "Мої думки з розпеченої сталі\nСтікали краплями солодкого сиропу.\nВсі брали пробу.\n\nМої слова із пережатим горлом\nХрипіли втомлено у темряву небесну.\nБуло чудесно.";
        $this->assertEquals("Мої думки з розпеченої сталі\nСтікали краплями солодкого сиропу.\nВсі брали пробу.\n...", truncate($str, 200));
    }
}