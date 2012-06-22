<?php

namespace PPPhperjp;

use Zend\Http\Response;
use Zend\Json\Json;

class File
{
    protected $fileArray;

    public function __construct(Response $response, $format)
    {
        if ('.json' === $format) {
            $decoded = Json::decode($response->getBody());
            $this->fileArray = $decoded['file'];
        }
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
