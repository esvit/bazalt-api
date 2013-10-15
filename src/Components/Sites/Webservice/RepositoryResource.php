<?php

namespace Components\Sites\Webservice;
use Bazalt\Rest\Response,
    Bazalt\Site\Model\Site;

/**
 * SiteResource
 *
 * @uri /sites/:id/repository
 */
class RepositoryResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getItem($id)
    {
        $cmd = 'cd /var/www/sites/ua2.biz/www/sites/test.ua2.biz && ';
        $cmd .= 'git remote show origin';
        $output = shell_exec($cmd);

        preg_match("#Fetch URL:(.*)#i", $output, $matches);
        print_r($matches);

        echo $output;exit;
        if (!$item) {
            return new Response(404, ['id' => 'Site not found']);
        }
        return new Response(Response::OK, $item->toArray());
    }

    /**
     * @method POST
     * @json
     */
    public function saveItem($id = null)
    {
        if (!\Bazalt\Auth::getUser()->hasPermission('admin.access')) {
            return new Response(Response::FORBIDDEN, ['user' => 'Permission denied']);
        }
        $dataValidator = \Bazalt\Site\Data\Validator::create($this->request->data);
        $item = ($id == null) ? Site::create() : Site::getById($id);
        if (!$item) {
            return new Response(Response::NOTFOUND, '404');
        }

        $dataValidator->field('domain')
            ->required()
            ->length(3, 255);

        $dataValidator->field('is_active')->bool();
        $dataValidator->field('is_multilingual')->bool();
        $dataValidator->field('is_allow_indexing')->bool();

        if (!$dataValidator->validate()) {
            return new Response(400, $dataValidator->errors());
        }

        $item->domain = $dataValidator['domain'];
        $item->is_active = $dataValidator['is_active'] ? 1 : 0;
        $item->is_multilingual = $dataValidator['is_multilingual'] ? 1 : 0;
        $item->is_allow_indexing = $dataValidator['is_allow_indexing'] ? 1 : 0;
        $item->save();

        return new Response(Response::OK, $item->toArray());
    }

    /**
     * @method DELETE
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function deleteItem($id)
    {
        $item = Site::getById((int)$id);

        if (!$item) {
            return new Response(400, ['id' => "Site not found"]);
        }
        $item->delete();
        return new Response(200, true);
    }
}