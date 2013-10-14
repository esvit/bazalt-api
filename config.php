<?php

define('SITE_DIR', __DIR__);

date_default_timezone_set('Europe/Kiev');

if (!defined('APPLICATION_ENV')) {
    define('APPLICATION_ENV', getenv('APPLICATION_ENV'));
}
define('DEVELOPMENT_STAGE', APPLICATION_ENV == 'development');
define('PRODUCTION_STAGE',  APPLICATION_ENV == 'production');
define('TESTING_STAGE',     APPLICATION_ENV == 'testing');

define('TEMP_DIR', SITE_DIR . '/tmp');
define('UPLOAD_DIR', realpath(SITE_DIR . '/uploads'));

$loader = require 'vendor/autoload.php';

use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

$run     = new Whoops\Run;
$handler = new PrettyPageHandler;
$handler->addDataTableCallback('ORM', \Bazalt\ORM\Exception\Query::whoopsDataTableCallback());

$jsonHandler = new JsonResponseHandler();
$jsonHandler->addTraceToOutput(true);
$jsonHandler->onlyForAjaxRequests(true);

$run->pushHandler($handler);
//$run->pushHandler($jsonHandler);
$run->pushHandler(function($exception, $inspector, $run) {
    http_response_code(500);
    return Whoops\Handler\Handler::DONE;
});
$run->allowQuit(true);
$run->register();

if (php_sapi_name() == 'cli-server') {
    // for correct ajax on cli server
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
}

// init image storage
\Bazalt\Thumbs\Image::initStorage(__DIR__ . '/static', 'http://localhost:8000/thumb.php?file=/static', __DIR__);
//\Bazalt\Thumbs\Image::initStorage(__DIR__ . '/images', 'http://s%s.mistinfo.com', __DIR__);

// init database
$connectionString = new \Bazalt\ORM\Adapter\Mysql([
    'server' => 'localhost',
    'port' => '3306',
    'database' => 'bazalt_cms',
    'username' => 'root',
    'password' => PRODUCTION_STAGE ? 'gjhndtqy777' : 'awdawd'
]);
\Bazalt\ORM\Connection\Manager::add($connectionString, 'default');

/*
// init elasticsearch plugin
\Bazalt\Search\ElasticaPlugin::setClient(new \Elastica\Client(array(
    'url' => 'http://experiments.equalteam.net:9200/',
)));
\Bazalt\Search\ElasticaPlugin::setDefaultIndex('test');
*/

// init session
\Bazalt\Session::setTimeout(30 * 24 * 60 * 60);
\Bazalt\Site::enableMultisiting(true);

require_once 'helpers/truncate.php';
require_once 'helpers/relativePath.php';
require_once 'helpers/translit.php';
require_once 'helpers/cleanUrl.php';