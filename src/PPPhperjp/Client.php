<?php
namespace PPPhperjp;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

use Zend\Serializer\Adapter\AdapterInteface as SerializerAdapter;

/**
  Phperjp
    zf lists phperjp
    zf info phperjp project-id
    zf create phperjp name description
    zf update phperjp project-id description
    zf destroy phperjp project-id
    zf deploy phperjp
    zf servers-create phperjp name fqdn root[=public] project-id[=default]
 *
 */

/**
 * ref ：ruby gem phper command
 * https://github.com/tumf/phper

Usage: phper [options]
        --version                    show version
        --help                       show this message
        --debug                      debug mode

Commands:
    help
    login
    logout
    list
    create
    destroy
    info
    keys
    keys:add
    keys:remove
    keys:clear
    servers
    servers:add
    servers:remove
    open
    db:init
    deploy
*/


class Client implements ServiceManagerAwareInterface
{
    protected $username;
    protected $password;

    protected $serviceManager;

    /**
     * @var Zend_Rest_Client
     */
    protected $restClient;

    protected $responseFormat = '.json';
    protected $outputAdapter = 'PhpCode';
    protected $serializer;

    public function __construct($username, $password, ServiceManager $serviceManager = null)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /****************
     * project command
     ***************/
    
    public function lists()
    {
        $client = $this->serviceManager->get('rest');
        //$client->getHttpClient()->setAuth($this->username, $this->password);
        $response = $client->restGet('/projects'.$this->responseFormat);
        
        $var = $this->handleResponse($response);

        var_dump($var);
    }
    
    // @todo listsで取得したものを保存し、一致するかのチェック
    public function info($projectId)
    {
        $response = $this->getRestClient()
                 ->restGet('/projects/'.$projectId.$this->responseFormat);

        $var = $this->handleResponse($response);

        $this->_registry
             ->getResponse()
             ->appendContent($var);
    }

    /**
     * create project
     * currently limited "5"
     *
     * @todo 名前制限？[0-9a-z]{1,10}　チェックのとき自動割り当ての対応
     *
     * @param string $name
     * @param string $description
     */
    public function create($name, $description = '')
    {
        $data = array('project' => compact('name', 'description'));
        $response = $this->getRestClient()
                 ->restPost('/projects'.$this->responseFormat, Zend\Json\Json::encode($data));

        $var = $this->handleResponse($response);

        $this->_registry
             ->getResponse()
             ->appendContent($var);
    }

    /**
     * update project (プロジェクトの更新)
     */
    public function update($projectId, $description)
    {
        $data = array('project' => compact('description'));

        $httpClient = $this->getRestClient()->getHttpClient();
        $httpClient->setEncType(Zend_Http_Client::ENC_FORMDATA);

        $response = $this->getRestClient()
                 ->restPut('/projects/'.$projectId.$this->responseFormat, $data);

        $this->_registry
             ->getResponse()
             ->appendContent($response->getMessage());
    }

    /**
     * phperコマンドにならってdestroy
     */
    public function destroy($projectId)
    {
        $response = $this->getRestClient()
                 ->restDelete('/projects/'.$projectId.$this->responseFormat);

        $this->_registry
             ->getResponse()
             ->appendContent($response->getMessage());
    }

    public function deploy($projectId)
    {
        $response = $this->getRestClient()
                 ->restGet('/projects/'.$projectId.'/deploy'.$this->responseFormat);

        $this->_registry
             ->getResponse()
             ->appendContent($response->getMessage());
    }

    /****************
     * key command
     ***************/

    public function keys()
    {
        $response = $this->getRestClient()
                 ->restGet('/keys'.$this->responseFormat);

        $var = $this->handleResponse($response);

        $this->_registry
             ->getResponse()
             ->appendContent($var);
    }

    /**
     * not tested.
     */
    public function keysCreate($public_key)
    {
        $response = $this->getRestClient()
                 ->restPost('/keys'.$this->responseFormat, Zend\Json\Json::encode(array('key' => compact('public_key'))));

        $var = $this->handleResponse($response);

        $this->_registry
             ->getResponse()
             ->appendContent($var);
    }

    /****************
     * server command
     ***************/

    public function servers($projectId)
    {
        $client = $this->getRestClient();
        $response = $client->restGet('/projects/'.$projectId.'/servers'.$this->responseFormat);
        
        $var = $this->handleResponse($response);

        $this->_registry
             ->getResponse()
             ->appendContent($var);
    }

    /**
     * create application server (アプリケーションサーバの作成)
     */
    public function serversCreate($projectId, $name = 0, $fqdn = 0, $root = 'public')
    {
        if (null === $projectId) throw new Exception('Default handle not implemented yet');

        $response = $this->getRestClient()
                 ->restGet('/projects/'.$projectId.'/servers/new'.$this->responseFormat);

        // @todo should check response
        $data = Zend\Json\Json::Decode($prev = $response->getBody());

        $default = $data['server'];
        $post = array('server' => array());
        $post['server']['name'] = ($name) ? $name :$default['name'];
        $post['server']['fqdn'] = ($fqdn) ? $fqdn :$default['fqdn'];
        $post['server']['root'] = ($root) ? $root :$default['root'];

        $response = $this->getRestClient()
                 ->restPost('/projects/'.$projectId.'/servers'.$this->responseFormat, $post);

        $var = $this->handleResponse($response);

        $this->_registry
             ->getResponse()
             ->appendContent($var);
    }

    public function serversDelete($projectId, $serverId)
    {
        $response = $this->getRestClient()
                 ->restDelete('/projects/'.$projectId.'/servers/'.$serverId.$this->responseFormat);

        $this->_registry
             ->getResponse()
             ->appendContent($response->getMessage());
    }

    public function hosts()
    {
    
    }

    /************************
     * files command
     *************************/

    public function files($projectId, $host, $name = null)
    {
        if ($name) {
            $path = sprintf('/projects/%s/hosts/%s/files/%s', $projectId, $host, rawurlencode($name)). $this->responseFormat;
        } else {
            $path = sprintf('/projects/%s/hosts/%s/files', $projectId, $host). $this->responseFormat;
        }

        $response = $this->getRestClient()->restGet($path);

        $var = $this->handleResponse($response);

        $this->_registry->getResponse()->appendContent($var);
    }

    public function filesModifiedGet($projectId, $host)
    {
        $path = sprintf('/projects/%s/hosts/%s/files/%s', $projectId, $host, rawurlencode('modified')). $this->responseFormat;
        $response = $this->getRestClient()->restGet($path);
        $modifieds = Zend\Json\Json::decode($response->getBody());

        //$this->_registry->getResponse()->appendContent(var_export($modified, true));
        foreach ($modifieds as $modified) {
            // @todo check すでにローカルにあるファイル
            $fileid = $modified['file']['id'];
            $path = sprintf('/projects/%s/hosts/%s/files/%s', $projectId, $host, $fileid). $this->responseFormat;
            $response = $this->getRestClient()->restGet($path);
            $fileObj = new Wozozo_Service_Phperjp_File($response, $this->responseFormat);
            $fileObj->save();
            $this->_registry->getResponse()->appendContent('--> '.$modified['file']['name']);
        }
    }

    protected function handleResponse($response)
    {
        if (!$response->isSuccess()) {
            throw new \Exception('Response returns Error '. $response->getStatusCode().' : '. $response->getReasonPhrase());
        }

        try {
            if ('.json' === $this->responseFormat) {
                $decode = \Zend\Json\Json::decode($response->getBody());
            } elseif ('.xml' === $this->responseFormat) {
                //@todo
                //var_dump($response);
            }

            //return $this->getSerializer()->serialize($decode);
            return var_dump($decode);
        } catch (Exception $e) {
            throw new UnexpectedValueException(
                                               'Exception occured :'. $e->getMessage().PHP_EOL.
                                               'Response body is :'. substr($response->getBody(), 0, 20). '..',
                                               $e->getCode(),
                                               $e
                                               );
        }
    }

    protected function getRestClient()
    {
        if (!$this->restClient instanceof Zend_Rest_Client) {

            $client = new Zend_Http_Client;
            $client->setAdapter('Zend_Http_Client_Adapter_Curl');
            
            if (!isset($config->username) or !isset($config->password)) {
                throw new Exception('Not Configured... "service.phperjp" in ".zf.ini"');
            }

            $client->setAuth($config->username, $config->password);
            Zend_Rest_Client::setHttpClient($client);
            $this->restClient = new Zend_Rest_Client('https://phper.jp');
        }

        return $this->restClient;
    }

    protected function getSerializer()
    {
        if (!$this->serializer instanceof Zend_Serializer_Adapter_AdapterInterface) {
            if ($outputAdapter = $this->_registry->getConfig()->service->phperjp->outputadapter) {
                $this->outputAdapter = $outputAdapter;
            }
            $this->serializer = Zend_Serializer::factory($this->outputAdapter);
        }

        return $this->serializer;
    }
}

