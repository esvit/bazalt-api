<?php

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    die('Update you PHP first. Needed 5.4.0 min. You are using ' . PHP_VERSION . ' now;');
}

define('SITE_DIR', __DIR__);
define('INSTALLER_DIR', __DIR__ . '/install');

require_once 'vendor/autoload.php';

/**
 * @uri /install
 */
class Installer extends \Tonic\Resource
{
    /**
     * @method GET
     * @provides text/html
     * @return \Tonic\Response
     */
    public function index()
    {
        return file_get_contents(INSTALLER_DIR . '/views/layout.html');
    }

    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function checkRequirements()
    {
        $load_ext = get_loaded_extensions();

        $result = [
            'env' => [
                'php54' => (version_compare(phpversion(), "5.4.0", ">=")),
                'mod_rewrite'  => (function_exists('apache_get_modules')) ? in_array('mod_rewrite', apache_get_modules()) : 'unknown',
            ],
            'php' => [
                'mbstring'  => in_array('mbstring', $load_ext),
                'pdo_mysql'  => in_array('pdo_mysql', $load_ext),
                'pdo'  => in_array('PDO', $load_ext),
                'gd'  => in_array('gd', $load_ext),
                'reflection'  => in_array('Reflection', $load_ext),
                'session'  => in_array('session', $load_ext),
                'json'  => in_array('json', $load_ext),
                'filter'  => in_array('filter', $load_ext),
                'curl'  => in_array('curl', $load_ext),
            ],
            /*'folders' => [
                'config_writable'   => touch(SITE_DIR . '/config.php') !== false && is_writable(SITE_DIR . '/config.php'),
                'uploads_writable'  => is_writable(SITE_DIR . '/uploads'),
                'static_writable'   => is_writable(SITE_DIR . '/static'),
                'tmp_writable'      => is_writable(SITE_DIR . '/tmp')
            ]*/
        ];
        return new \Tonic\Response(200, $result);
    }

    public static function loadDump(PDO $dbh, $file)
    {
        $fp = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $query = '';
        foreach ($fp as $line) {
            if ($line != '' && strpos($line, '--') === false) {
                $query .= $line;
                if (substr($query, -1) == ';') {
                    $dbh->exec($query);
                    $query = '';
                }
            }
        }
    }

    /**
     * @method POST
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function connectToDatabase()
    {
        $data = (array)$this->request->data;
        if (empty($data['password'])) {
            $data['password'] = '';
        }
        $result = [];
        try {
            // create database
            $connectionStr = sprintf('mysql:host=%s;port=%d', $data['host'], $data['port']);
            if (isset($data['create']) && $data['create'] == true) {
                $dbh = new PDO($connectionStr, $data['user'], $data['password']);
                $dbh->exec("CREATE DATABASE `" . $data['database'] . "`");
            }
            // upload database dump
            $connectionDbStr = $connectionStr . sprintf(';dbname=%s', $data['database']);
            $dbh = new PDO($connectionDbStr, $data['user'], $data['password']);
            self::loadDump($dbh, INSTALLER_DIR . '/install.sql');
            foreach (glob(INSTALLER_DIR . '/components/*.sql') as $file) {
                self::loadDump($dbh, $file);
            }
            // select languages
            $sql = 'SELECT * FROM cms_languages';
            $res = $dbh->query($sql, PDO::FETCH_ASSOC);
            foreach ($res as $row) {
                $result []= $row;
            }
            $dbh = null;
        } catch (PDOException $e) {
            return new \Tonic\Response(500, $e->getMessage());
        }
        return new \Tonic\Response(200, $result);
    }

    /**
     * @method PUT
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function createSite()
    {
        $data = (array)$this->request->data;
        if (empty($data['password'])) {
            $data['password'] = '';
        }
        $config = file_get_contents(INSTALLER_DIR . '/config.example');
        $connection = (array)$data['connection'];
        if (empty($connection['port'])) {
            $connection['port'] = 3306;
        }
        if (empty($connection['password'])) {
            $connection['password'] = '';
        }
        foreach ($connection as $key => $value) {
            $config = str_replace('%' . $key . '%', $value, $config);
        }
        file_put_contents(SITE_DIR . '/config.php', $config);
        try {
            $connectionDbStr = sprintf('mysql:host=%s;port=%d;dbname=%s', $connection['host'], $connection['port'], $connection['database']);
            $dbh = new PDO($connectionDbStr, $connection['user'], $connection['password']);

            $stmt = $dbh->prepare("UPDATE cms_users SET login = :login, password = :password WHERE id = 1");
            $stmt->bindParam(':login', $data['user']);
            $stmt->bindParam(':password', hash('sha512', $data['password']));
            $stmt->execute();

            $stmt = $dbh->prepare("UPDATE cms_sites SET title = :title, language_id = :language, languages = :language WHERE id = 1");
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':language', $data['language']);
            $stmt->execute();
        } catch (PDOException $e) {
            return new \Tonic\Response(500, $e->getMessage());
        }
        return new \Tonic\Response(200, true);
    }

    protected function json()
    {
        $this->before(function ($request) {
            if ($request->contentType == "application/json") {
                $request->data = json_decode($request->data);
            }
        });
        $this->after(function ($response) {
            $response->contentType = "application/json";
            if (isset($_GET['jsonp'])) {
                $response->body = $_GET['jsonp'].'('.json_encode($response->body).');';
            } else {
                $response->body = json_encode($response->body);
            }
        });
    }
}


if (php_sapi_name() == 'cli') {
    if (count($argv) < 2) {
        echo 'Usage `php install.php database`';
        exit;
    }
    $data = [
        'host' => 'localhost',
        'port' => 3306,
        'create' => true,
        'database' => $argv[1],
        'user' => 'root',
        'password' => 'awdawd'
    ];

    // create database
    $connectionStr = sprintf('mysql:host=%s;port=%d', $data['host'], $data['port']);
    if (isset($data['create']) && $data['create'] == true) {
        $dbh = new PDO($connectionStr, $data['user'], $data['password']);
        $dbh->exec("CREATE DATABASE `" . $data['database'] . "`");
    }
    // upload database dump
    $connectionDbStr = $connectionStr . sprintf(';dbname=%s', $data['database']);
    $dbh = new PDO($connectionDbStr, $data['user'], $data['password']);
    Installer::loadDump($dbh, INSTALLER_DIR . '/install.sql');
    foreach (glob(INSTALLER_DIR . '/components/*.sql') as $file) {
        Installer::loadDump($dbh, $file);
    }
    exit;
}

$app = new \Tonic\Application([]);
$request = new \Tonic\Request([
    'uri' => '/install'
]);

try {
    $resource = $app->getResource($request);

    $response = $resource->exec();
} catch (\Tonic\NotFoundException $e) {
    $response = new \Tonic\Response(404, $e->getMessage());
} catch (\Tonic\Exception $e) {
    $response = new \Tonic\Response($e->getCode(), $e->getMessage());
}
$response->output();