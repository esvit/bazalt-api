<?php

namespace CMS;

class ngTable
{
    /**
     * @var \Bazalt\ORM\Collection
     */
    protected $collection = null;

    protected $sortableColumns = [];

    protected $filterColumns = [];

    public function __construct(&$collection)
    {
        $this->collection = $collection;
    }

    public function sortableBy($column, $callback = null)
    {
        $this->sortableColumns[$column] = $callback === null ? false : $callback;

        return $this;
    }

    public function filterBy($column, $callback = null)
    {
        $this->filterColumns[$column] = $callback === null ? false : $callback;

        return $this;
    }

    public function exec($params = [])
    {
        if (!isset($params['page'])) {
            $params['page'] = 1;
        }
        if (!isset($params['count'])) {
            $params['count'] = 10;
        }

        // filter
        if (isset($params['filter'])) {
            foreach ($params['filter'] as $columnName => $value) {
                if (!isset($this->filterColumns[$columnName])) {
                    continue;
                }
                $value = urldecode($value);
                if ($this->filterColumns[$columnName] !== false && is_callable($this->filterColumns[$columnName])) {
                    $callback = $this->filterColumns[$columnName];
                    $callback($this->collection, $columnName, $value);
                } else {
                    $this->collection->andWhere('`' . $columnName . '` = ?', $value);
                }
            }
        }

        // sorting
        if (isset($params['sorting'])) {
            $this->collection->clearOrderBy();
            foreach ($params['sorting'] as $key => $item) {
                $firstLetter = $item[0];
                if ($firstLetter == '-' || $firstLetter == '+') {
                    $direction = $item[0] == '+' ? 'ASC' : 'DESC';
                    $columnName = substr($item, 1);
                } else {
                    $direction = $item == 'asc' ? 'ASC' : 'DESC';
                    $columnName = $key;
                }
                if (!isset($this->sortableColumns[$columnName])) {
                    continue;
                }
                if ($this->sortableColumns[$columnName] !== false && is_callable($this->sortableColumns[$columnName])) {
                    $callback = $this->sortableColumns[$columnName];
                    $callback($this->collection, $columnName, $direction);
                } else {
                    $this->collection->addOrderBy('`' . $columnName . '` ' . $direction);
                }
            }
        }
        $this->collection->page((int)$params['page'])
                         ->countPerPage((int)$params['count']);
    }

    public function fetch($params = [], $callback = null, $className = null)
    {
        $this->exec($params);

        $return = [];
        $result = $this->collection->fetchPage($className);

        foreach ($result as $k => $item) {
            if ($callback && is_callable($callback)) {
                $res = $item->toArray();
                $res = $callback($res, $item);
            } else if ($item instanceof \stdClass) {
                $res = (array)$item;
            } else {
                $res = $item->toArray();
            }

            // filter fields
            if (isset($params['fields'])) {
                $fields = array_flip(explode(',', $params['fields']));
                $res = array_intersect_key($res, $fields);
            }
            $return[$k] = $res;
        }

        $data = [
            'data' => $return,
            'pager' => [
                'current'       => $this->collection->page(),
                'count'         => $this->collection->getPagesCount(),
                'total'         => $this->collection->count(),
                'countPerPage'  => $this->collection->countPerPage()
            ]
        ];
        //$data['sql'] = $this->collection->toSQL();
        return $data;
    }
}