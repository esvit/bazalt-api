<?php

require_once 'config.php';

$modules = [
    $loader->findFile('Components\\Files\\Webservice\\FilesResource'),
    $loader->findFile('Components\\Pages\\Webservice\\Pages\\CommentsResource'),
    $loader->findFile('Components\\Pages\\Webservice\\Pages\\Comments\\RatingResource'),
    $loader->findFile('Components\\Pages\\Webservice\\Pages\\RatingResource'),
    $loader->findFile('Components\\Pages\\Webservice\\TagsResource'),
    $loader->findFile('Components\\Pages\\Webservice\\ImagesResource'),
    $loader->findFile('Components\\Pages\\Webservice\\CategoryResource'),
    $loader->findFile('Components\\Pages\\Webservice\\CategoriesResource'),
    $loader->findFile('Components\\Pages\\Webservice\\PageResource'),
    $loader->findFile('Components\\Pages\\Webservice\\PagesResource'),
    $loader->findFile('Components\\Widgets\\Webservice\\WidgetsResource'),
    $loader->findFile('Components\\Menu\\Webservice\\MenuResource'),
    $loader->findFile('Components\\Menu\\Webservice\\MenuTypesResource'),
    $loader->findFile('Components\\Menu\\Webservice\\ElementsResource'),
    $loader->findFile('Components\\Users\\Webservice\\User\\AvatarResource'),
    $loader->findFile('Bazalt\\Auth\\Webservice\\UserResource'),
    $loader->findFile('Bazalt\\Auth\\Webservice\\UsersResource'),
    $loader->findFile('Bazalt\\Auth\\Webservice\\RoleResource'),
    $loader->findFile('Bazalt\\Auth\\Webservice\\SessionResource')
];

$app = new Tonic\Application(array(
    'load' => $modules
));
if (!isset($_SERVER['PATH_INFO'])) {
    exit('Not found $_SERVER[PATH_INFO]');
}
$request = new Tonic\Request(array(
    'uri' => $_SERVER['PATH_INFO']
));

try {
    $resource = $app->getResource($request);
    $response = $resource->exec();
} catch (Tonic\NotFoundException $e) {
    $response = new Tonic\Response(404, $e->getMessage());
} catch (Tonic\Exception $e) {
    echo $e->getMessage();
    $response = new Tonic\Response($e->getCode(), $e->getMessage());
}
$response->output();
