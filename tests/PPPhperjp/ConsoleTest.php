<?php
namespace PPPhperjpTest;

use Zend\ServiceManager\ServiceManager;

use PPPhperjp\Client;
use PPPhperjp\Console;

class ConsoleTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        global $username, $password;
        $console = new Console(new Client($username, $password));
        $console->optionHelp();
    }
}

