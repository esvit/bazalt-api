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
    protected function getRepository($site)
    {
        $path = '/var/www/sites/ua2.biz/www/sites/' . $site->domain;
        $client = new \Gitter\Client;
        return [$client, $client->getRepository($path)];
    }

    /**
     * @method GET
     * @json
     */
    public function getItem($id)
    {
        $site = Site::getById((int)$id);
        if (!$site) {
            return new Response(Response::NOTFOUND, ['id' => 'Site not found']);
        }
        try {
            /** @var \Gitter\Repository $repository */
            list($client, $repository) = $this->getRepository($site);
        } catch (\RuntimeException $ex) {
            return new Response(Response::BADREQUEST, ['id' => 'No repository']);
        }

        $output = $client->run($repository, 'remote show origin');

        if (preg_match("#Fetch URL: (.*)#i", $output, $matches)) {
            $data = [
                'type'       => 'git',
                'repository' => $matches[1],
                'commits'    => []
            ];
            /** @var \Gitter\Model\Commit\Commit[] $commits */
            $commits = $repository->getCommits('-5');
            foreach ($commits as $commit) {
                $data['commits'] []= [
                    'hash' => $commit->getHash(),
                    'date' => $commit->getDate(),
                    'message' => $commit->getMessage(),
                    'commiter' => [
                        'name' => $commit->getCommiter()->getName(),
                        'email' => $commit->getCommiter()->getEmail()
                    ]
                ];
            }
            return new Response(Response::OK, $data);
        }
        return new Response(Response::BADREQUEST, ['id' => 'Something wrong']);
    }

    /**
     * @method PUT
     * @json
     */
    public function updateRepository($id)
    {
        $site = Site::getById((int)$id);
        if (!$site) {
            return new Response(Response::NOTFOUND, ['id' => 'Site not found']);
        }
        try {
            /** @var \Gitter\Repository $repository */
            list($client, $repository) = $this->getRepository($site);
        } catch (\RuntimeException $ex) {
            return new Response(Response::BADREQUEST, ['id' => 'No repository']);
        }

        $repository->pull();
        return new Response(Response::OK, ['status' => 'OK']);
    }

    /**
     * @method POST
     * @json
     */
    public function createRepository($id)
    {
        $site = Site::getById((int)$id);
        if (!$site) {
            return new Response(Response::NOTFOUND, ['id' => 'Site not found']);
        }

        $path = '/var/www/sites/ua2.biz/www/sites/' . $site->domain;
        $client = new \Gitter\Client;

        $repository = $client->createRepository($path);
        $client->run($repository, 'remote add origin ' . $this->request->data->repository);
        $repository->checkout('-b master');
        $client->run($repository, 'pull origin master');


        return new Response(Response::OK, ['id' => 'OK']);
    }
}