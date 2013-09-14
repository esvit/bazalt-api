<?php

define('SITE_DIR', __DIR__);

date_default_timezone_set('Europe/Kiev');

if (!defined('APPLICATION_ENV')) {
    define('APPLICATION_ENV', getenv('APPLICATION_ENV'));
}
define('DEVELOPMENT_STAGE', APPLICATION_ENV == 'development');
define('PRODUCTION_STAGE',  APPLICATION_ENV == 'production');
define('TESTING_STAGE',     APPLICATION_ENV == 'testing');

$loader = require 'vendor/autoload.php';

use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

$run     = new Whoops\Run;
$handler = new PrettyPageHandler;

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
\Bazalt\Thumbs\Image::initStorage(__DIR__ . '/static', '/thumb.php?file=/static', __DIR__ . '/../');
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

function translit($text)
{
    $transArr  = array (
        'а' => 'a', 'б' => 'b',  'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'yo', 'ж' => 'j', 'з' => 'z', 'и' => 'i',
        'й' => 'i', 'к' => 'k',  'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p',  'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'y', 'ф' => 'f',  'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
        'ш' => 'sh','щ' => 'sh', 'ы' => 'i', 'э' => 'e', 'ю' => 'u',
        'я' => 'ya',
        'А' => 'A',  'Б' => 'B',  'В' => 'V', 'Г' => 'G', 'Д' => 'D',
        'Е' => 'E',  'Ё' => 'Yo', 'Ж' => 'J', 'З' => 'Z', 'И' => 'I',
        'Й' => 'I',  'К' => 'K',  'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O',  'П' => 'P',  'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'Y',  'Ф' => 'F',  'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch',
        'Ш' => 'Sh', 'Щ' => 'Sh', 'Ы' => 'I', 'Э' => 'E', 'Ю' =>'U',
        'Я' => 'Ya',
        'ь' => '',  'Ь' => '',  'ъ' => '',  'Ъ' => '',
        'ї' => 'j', 'і' => 'i', 'ґ' => 'g', 'є' => 'ye',
        'Ї' => 'J', 'І' => 'I', 'Ґ' => 'G', 'Є' => 'YE'
    );
    return strtr($text, $transArr);
}

function cleanUrl($url, $replace = array(), $delimiter = '-')
{
    //$url = self::decodeCharReferences($url);

    if (!empty($replace)) {
        $url = str_replace((array)$replace, ' ', $url);
    }

    $replaceSymbols = array(
        '«', '»', '”', '“', '№', '—', '–', "\xC2\xA0" /* no break space */
    );

    // remove symbols
    $url = preg_replace('/[\\x00-\\x19\\x21-\\x2F\\x3A-\\x40\\x5B-\\x60\\x7B-\\x7F]/', ' ', $url);

    $url = str_replace($replaceSymbols, ' ', $url);

    $url = preg_replace("/\s+/", ' ', $url);

    $url = str_replace('?', '', $url);

    $url = preg_replace("/[\/_|+ -]+/", $delimiter, $url);

    $url = mb_strToLower(trim($url, $delimiter));

    return $url;
}