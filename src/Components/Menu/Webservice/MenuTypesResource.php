<?php

namespace Components\Menu\Webservice;

use \Bazalt\Rest\Response,
    \Bazalt\Session,
    \Bazalt\Data as Data;

use Components\Menu\Model\Element;
use Whoops\Example\Exception;

/**
 * @uri /menu/types
 */
class MenuTypesResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function getTypes()
    {
        $result = [];
        $types = Element::getMenuTypes();

        foreach ($types as $className => $menuItem) {
            $component = $menuItem->component()->config();
            $result []= [
                'component_id' => $component->id,
                'component_title' => $component->title,
                'class' => $className,
                'title' => $menuItem->getItemType()
            ];
        }
        return new Response(200, $result);
    }
}
