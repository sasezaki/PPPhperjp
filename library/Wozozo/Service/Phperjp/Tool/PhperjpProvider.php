<?php

/**
 * How to Use with zf command.
 *
 * push your include path this file.
 * 
 * $zf enable config.provider=Wozozo_Service_Phperjp_Tool_PhperjpProvider
 *
 * and then..

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
参考：rubyのphperコマンド

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

require_once 'Zend/Tool/Framework/Provider/Abstract.php';


class Wozozo_Service_Phperjp_Tool_PhperjpProvider
    extends Zend_Tool_Framework_Provider_Abstract
{
    /**
     * @var Zend_Rest_Client
     */
    protected $restClient;

    protected $responseFormat = '.json';
    protected $outputAdapter = 'PhpCode';
    protected $serializer;

    /****************
     * project command
     ***************/
    
    public function lists()
    {
        $client = $this->getRestClient();
        $response = $client->restGet('/projects'.$this->responseFormat);
        
        $var = $this->handleResponse($response);

        $this->_registry
             ->getResponse()
             ->appendContent($var);
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
                 ->restPost('/projects'.$this->responseFormat, Zend_Json::encode($data));

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
             ->appendContent($var->getStatus());
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
                 ->restPost('/keys'.$this->responseFormat, Zend_Json::encode(array('key' => compact('public_key'))));

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
     *
     */
    public function serversCreate($projectId, $name = 0, $fqdn = 0, $root = 'public')
    {
        if (null === $projectId) throw new Exception('Default handle not implemented yet');

        $response = $this->getRestClient()
                 ->restGet('/projects/'.$projectId.'/servers/new'.$this->responseFormat);

        // @todo should check response
        $data = Zend_Json::Decode($prev = $response->getBody());

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

    protected function handleResponse($response)
    {
        if (!$response->isSuccessful()) {
            throw new Exception('Response returns Error '. $response->getStatus().' : '. $response->getMessage());
        }

        try {
            if ('.json' === $this->responseFormat) {
                $decode = Zend_Json::decode($response->getBody());
            } elseif ('.xml' === $this->responseFormat) {
                //@todo
                //var_dump($response);
            }

            return $this->getSerializer()->serialize($decode);
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
            $config = $this->_registry->getConfig()->service->phperjp;

            $client = new Zend_Http_Client;
            $client->setAdapter('Zend_Http_Client_Adapter_Curl');
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
