<?php
namespace PPPhperjpTest;

use Zend\ServiceManager\ServiceManager;

use PPPhperjp\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        global $username, $password;
        $client = new Client($username, $password);
        $ret = $client->lists();
        var_dump($ret);
        //$this->assertEquals('text/html', $c['Content-type']);
    }
}

