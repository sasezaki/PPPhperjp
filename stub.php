#!/usr/bin/env php
<?php
try {
    Phar::mapPhar();
} catch (Exception $e) {
    echo "Cannot process PPPhperjp phar:" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    exit -1;
}
function PPPhperjp_autoload($class)
{
    $class = str_replace(array('_', '\\'), '/', $class);
    if (file_exists('phar://' . __FILE__ . '/PPPhperjp-@PACKAGE_VERSION@/php/' . $class . '.php')) {
        return include 'phar://' . __FILE__ . '/PPPhperjp-@PACKAGE_VERSION@/php/' . $class . '.php';
    }
}
spl_autoload_register("PPPhperjp_autoload");
$phar = new Phar(__FILE__);
$sig  = $phar->getSignature();
define('PPPhperjp_SIG', $sig['hash']);
define('PPPhperjp_SIGTYPE', $sig['hash_type']);

$username = ($username) ?: '***';
$password = ($password) ?: '***';

if (PHP_SAPI == 'cli') {
    $console = new PPPhperjp\Console(new PPPhperjp\Client($username, $password));
    if (!isset($argv[1])) {
        $console->optionHelp();
    }
    if ($argv[1]) {
        array_shift($argv);
        $method = array_shift($argv);
        $console->run($method, ($argv) ?: array());
    }   
}

__HALT_COMPILER();
