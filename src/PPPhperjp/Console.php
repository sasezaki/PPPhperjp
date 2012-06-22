<?php

namespace PPPhperjp;

use ReflectionClass;

class Console
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * $ php phper.phar --help
     */
    public function optionHelp()
    {
        $ref = new ReflectionClass($this->client);
        foreach ($ref->getMethods() as $method) {
            if (!$method->isPublic()) continue;
            if (strpos($method->getName(), '__') === 0) continue; // PHP common magics

            $name = $method->getName();
            $params = $method->getParameters();
            // should mark as color Parameter is require or option
            $normalizeParams = array_shift($params); //todo
            $this->output($name, $normalizeParams);
        }
    }

    public function run($method, $args = array())
    {
        //get_class_methods($method);
        //$method = $this->unfilter
        $ret = call_user_func_array(array($this->client, $method), $args);
        $this->output($ret);
    }

    protected function output($o)
    {
        foreach (func_get_args() as $o) {
            echo $o;
        }

        echo PHP_EOL;
    }
}
