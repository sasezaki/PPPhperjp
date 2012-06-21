<?php

namespace PPPhperjp;

class File
{
    //protected $response;
    //protected $responseFormat;
    protected $fileArray;

    public function __construct(Zend_Http_Response $response, $format)
    {
        if ('.json' === $format) {
            $decoded = Zend_Json::decode($response->getBody());
            $this->fileArray = $decoded['file'];
        }
        //$this->response = $response;
        //$this->responseFormat = $format;
    }

    public function getName()
    {
        return $this->fileArray['name'];
    }

    public function getContents()
    {
        return $this->fileArray['contents'];
    }

    // @todo 
    public function save($path = null)
    {
        file_put_contents($this->getName(), $this->getContents());
    }
}
