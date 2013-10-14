<?php

namespace tests\Pages\Webservice\Pages;

use Bazalt\Rest;

class ngTableTest extends \tests\BaseCase
{
    protected $site = null;

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
    }

    public function createPages($pages)
    {
        foreach ($pages as $page) {
            $p = \Components\Pages\Model\Page::create();
            foreach ($page as $field => $value) {
                $p->{$field} = $value;
            }
            $p->save();
        }
    }

    public function testExec()
    {
        $pagesCollection = \Components\Pages\Model\Page::getCollection();

        $table = new \CMS\ngTable($pagesCollection);

        $table->sortableBy('title')
              ->filterBy('title', function($collection, $value) {
                $collection->andWhere('title LIKE ?', '%' . $value . '%');
            });

        $this->assertEquals($pagesCollection->toSQL(), 'SELECT f.* FROM com_pages_pages AS f  RIGHT JOIN com_pages_pages_locale AS ref ON ref.id = f.id WHERE  (f.site_id = "' . $this->site->id . '") ORDER BY created_at DESC ');

        $params = [
            'sorting' => ['-title']
        ];

        $table->exec($params);

        $this->assertEquals($pagesCollection->toSQL(), 'SELECT f.* FROM com_pages_pages AS f  RIGHT JOIN com_pages_pages_locale AS ref ON ref.id = f.id WHERE  (f.site_id = "' . $this->site->id . '") ORDER BY `title` DESC ');
    }

    public function testFetch()
    {
        $pagesCollection = \Components\Pages\Model\Page::getCollection();

        $table = new \CMS\ngTable($pagesCollection);

        $table->sortableBy('title');

        $this->createPages([
            [
                'title' => ['en' => 'Test 1']
            ],
            [
                'title' => ['en' => 'Test 2']
            ]
        ]);

        $params = [
            'sorting' => ['-title']
        ];

        $this->assertEquals($table->fetch($params, function($item) {
            return ['title' => $item['title']];
        }), [
            'data' => [
                ['title' => ['en' => 'Test 2', 'orig' => 'en']],
                ['title' => ['en' => 'Test 1', 'orig' => 'en']]
            ],
            'pager' => [
                'current' => 1,
                'count' => 1,
                'total' => 2,
                'countPerPage' => 10
            ]
        ]);

        $params = [
            'sorting' => ['+title']
        ];

        $this->assertEquals($table->fetch($params, function($item) {
            return ['title' => $item['title']];
        }), [
            'data' => [
                ['title' => ['en' => 'Test 1', 'orig' => 'en']],
                ['title' => ['en' => 'Test 2', 'orig' => 'en']]
            ],
            'pager' => [
                'current' => 1,
                'count' => 1,
                'total' => 2,
                'countPerPage' => 10
            ]
        ]);

        $params = [
            'sorting' => [ 'title' => 'desc' ]
        ];

        $this->assertEquals($table->fetch($params, function($item) {
            return ['title' => $item['title']];
        }), [
            'data' => [
                ['title' => ['en' => 'Test 2', 'orig' => 'en']],
                ['title' => ['en' => 'Test 1', 'orig' => 'en']]
            ],
            'pager' => [
                'current' => 1,
                'count' => 1,
                'total' => 2,
                'countPerPage' => 10
            ]
        ]);

        $params = [
            'sorting' => [ 'title' => 'desc' ]
        ];

        $table->sortableBy('title', function($collection, $columnName, $direction) {
            $collection->andWhere($columnName . ' != "Test 2"')
                       ->orderBy($columnName . ' ' . $direction);
        });

        $this->assertEquals($table->fetch($params, function($item) {
            return ['title' => $item['title']];
        }), [
            'data' => [
                ['title' => ['en' => 'Test 1', 'orig' => 'en']]
            ],
            'pager' => [
                'current' => 1,
                'count' => 1,
                'total' => 1,
                'countPerPage' => 10
            ]
        ]);
    }

    public function testFields()
    {
        $pagesCollection = \Components\Pages\Model\Page::getCollection();

        $table = new \CMS\ngTable($pagesCollection);

        $table->sortableBy('title');

        $this->createPages([
            [
                'title' => ['en' => 'Test 1']
            ],
            [
                'title' => ['en' => 'Test 2']
            ]
        ]);

        $params = [
            'sorting' => ['-title'],
            'fields' => 'title'
        ];

        $this->assertEquals([
            'data' => [
                ['title' => ['en' => 'Test 2', 'orig' => 'en']],
                ['title' => ['en' => 'Test 1', 'orig' => 'en']]
            ],
            'pager' => [
                'current' => 1,
                'count' => 1,
                'total' => 2,
                'countPerPage' => 10
            ]
        ], $table->fetch($params));
    }
}