<?php
error_reporting( E_ALL | E_STRICT );

require_once dirname(__DIR__).'/vendor/SplClassLoader.php';

$loader = new SplClassLoader('PPPhperjp', dirname(__DIR__).'/src/');
$loader->register();


