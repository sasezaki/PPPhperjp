<?php
namespace PPPhperjpTest;

use Zend\ServiceManager\ServiceManager;

use PPPhperjp\Client;

// patch 
class RestClient extends \Zend\Rest\Client\RestClient
{
    protected function prepareRest($path)
    {
        global $username, $password;
        parent::prepareRest($path);
        $this->getHttpClient()->setAuth($username, $password);
    }
}

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        global $username, $password;

        $sm = new ServiceManager;
        $sm->setFactory('http', function() use ($username, $password) {
                $http = new \Zend\Http\Client();
                $http->setAdapter(new \Zend\Http\Client\Adapter\Curl);
                return $http;
        });
        $sm->setFactory('rest', function() use ($sm) {
            //$rest = new \Zend\Rest\Client\RestClient('https://phper.jp');
            $rest = new RestClient('https://phper.jp');
            $rest->setHttpClient($sm->get('http'));
            return $rest;
        });
        $client = new Client($username, $password);

        $client->setServiceManager($sm);
        $client->lists();
        
        //$this->assertEquals('text/html', $c['Content-type']);
    }
}

