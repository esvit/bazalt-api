<?php

namespace Components\Pages\Webservice;

use \Bazalt\Rest\Response,
    \Bazalt\Session,
    \Bazalt\Data as Data;
use Bazalt\Site\Data\Validator;
use Components\Pages\Model\Category;

/**
 * @uri /pages/categories/:category_id
 */
class CategoryResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getItem($id)
    {
        $item = Category::getById($id);
        if (!$item) {
            return new Response(400, ['id' => 'Category not found']);
        }
        return new Response(Response::OK, $item->toArray());
    }

    /**
     * Create menu element in menu
     *
     * @method PUT
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function createOrMoveMenuElement($id)
    {
        $data = (array)$this->request->data;
        $data['id'] = $id;
        $data['insert'] = isset($_GET['insert']) ? $_GET['insert'] : false;
        $data['move'] = isset($_GET['move']) ? $_GET['move'] : false;
        $data['before'] = isset($_GET['before']) ? $_GET['before'] : false;
        $data = Validator::create($data);

        $category = Category::getSiteRootCategory();
        $prevElement = null;
        $isInserting = $data['insert'] == 'true';
        $isMoving = $data['move'] == 'true';

        $data->field('id')->validator('exist_element', function($value) use (&$category) {
            return empty($value) || ($category = Category::getById((int)$value));
        }, "Category dosn't exists");

        if ($isMoving) {
            $data->field('before')->required()->validator('exist_parent', function($value) use (&$category, &$prevElement) {
                $prevElement = Category::getById((int)$value);

                return ($prevElement != null) && ($prevElement->site_id == $category->site_id);
            }, "Category dosn't exists");
        }

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        if ($isMoving) {
            if ($isInserting) {
                if (!$prevElement->Elements->moveIn($category)) {
                    throw new \Exception('Error when procesing menu operation');
                }
            } else {
                if (!$prevElement->Elements->moveAfter($category)) {
                    throw new \Exception('Error when procesing menu operation');
                }
            }
            $newElement = $category;
        } else {
            $newElement = Category::create();
            $newElement->title = 'New category';

            // insert as first element
            if ($isInserting) {
                if (!$category->Elements->insert($newElement)) {
                    throw new \Exception('Insert failed: 2');
                }
            } else {
                if (!$category->Elements->insertAfter($newElement)) {
                    throw new \Exception('Insert failed: 3');
                }
            }
        }

        return new Response(200, $newElement->toArray());
    }

    /**
     * @method POST
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function saveCategory($category_id = null)
    {
        $dataValidator = Validator::create($this->request->data);
        $category = ($category_id != null) ? Category::getById($category_id) : Category::create();

        if (!$dataValidator->validate()) {
            return new Response(400, $dataValidator->errors());
        }
        $category->title = $dataValidator['title'];
        $category->description = $dataValidator['description'];
        $category->url = $dataValidator['url'];
        $category->is_published = $dataValidator['is_published'] == 'true';
        $category->save();

        return new Response(200, $category->toArray());
    }

    /**
     * @method DELETE
     * @json
     */
    public function deleteItem($id = null)
    {
        $item = Category::getById($id);
        if (!$item) {
            return new Response(Response::NOTFOUND, '404');
        }
        $item->Elements->removeAll();
        if ($item->depth == 0) {
            $item->delete();
        } else {
            $item->Elements->getParent()->Elements->remove($item);
        }
        return new Response(200, true);
    }
}