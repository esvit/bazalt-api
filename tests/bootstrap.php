<?php

namespace tests;

define('APPLICATION_ENV', 'testing');

require_once(is_file(__DIR__ . '/../vendor/autoload.php') ? (__DIR__ . '/../vendor/autoload.php') : '../vendor/autoload.php');

$loader = new \Composer\Autoload\ClassLoader();
$loader->add('tests', __DIR__ . '/..');
$loader->register();

$dbParams = array(
    'server' => $GLOBALS['db_host'],
    'username' => $GLOBALS['db_username'],
    'password' => $GLOBALS['db_password'],
    'database' => $GLOBALS['db_name'],
    'port' => $GLOBALS['db_port']
);

$connectionString = new \Bazalt\ORM\Adapter\Mysql($dbParams);
\Bazalt\ORM\Connection\Manager::add($connectionString, 'test');
\Bazalt\ORM\Connection\Manager::add($connectionString, 'default');


$full = true;
if ($full) {
    $dbh = new \PDO($connectionString->toPDOConnectionString(), $connectionString->getUser(), $connectionString->getPassword());
    $dbh->exec("DROP DATABASE `" . $connectionString->getDatabase() . "`");
    $dbh->exec("CREATE DATABASE `" . $connectionString->getDatabase() . "`");
    $dbh->exec("USE `" . $connectionString->getDatabase() . "`");

    function loadDump(\PDO $dbh, $file)
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


    define('INSTALLER_DIR', realpath(__DIR__ . '/../install'));
    loadDump($dbh, __DIR__ . '/../vendor/bazalt/site/install.sql');
    loadDump($dbh, __DIR__ . '/../vendor/bazalt/auth/install.sql');
    loadDump($dbh, __DIR__ . '/../vendor/bazalt/orm/tests/sakila/sakila.sql');
    loadDump($dbh, INSTALLER_DIR . '/install.sql');

    foreach (glob(INSTALLER_DIR . '/components/*.sql') as $file) {
        loadDump($dbh, $file);
    }
}

require_once __DIR__ . '/../config.php';