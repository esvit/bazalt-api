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

    $loader->findFile('Components\\Payments\\Webservice\\TransactionResource'),

    $loader->findFile('Components\\Sites\\Webservice\\RepositoryResource'),
    $loader->findFile('Components\\Sites\\Webservice\\SiteResource'),
    $loader->findFile('Components\\Sites\\Webservice\\SitesResource'),

    $loader->findFile('Components\\Menu\\Webservice\\MenuResource'),
    $loader->findFile('Components\\Menu\\Webservice\\MenuTypesResource'),
    $loader->findFile('Components\\Menu\\Webservice\\ElementsResource'),

    $loader->findFile('Components\\Users\\Webservice\\User\\GiftResource'),
    $loader->findFile('Components\\Users\\Webservice\\GiftsResource'),
    $loader->findFile('Components\\Users\\Webservice\\User\\GiftsResource'),
    $loader->findFile('Components\\Users\\Webservice\\User\\MessageResource'),
    $loader->findFile('Components\\Users\\Webservice\\User\\MessagesResource'),
    $loader->findFile('Components\\Users\\Webservice\\User\\AvatarResource'),
    $loader->findFile('Components\\Users\\Webservice\\User\\RoleResource'),
    $loader->findFile('Components\\Users\\Webservice\\UserResource'),
    $loader->findFile('Components\\Users\\Webservice\\UsersResource'),
    $loader->findFile('Components\\Users\\Webservice\\SessionResource')
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

// CORS headers
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

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