<?php

namespace DGC\MongoODMBundle\Service;

use MongoDB\Client;
use DGC\MongoODMBundle\Exception\ConnectionNotFoundException;

class ConnectionManager
{
    protected $config;

    protected $connections;

    public function __construct(array $config)
    {
        $this->config = $config;
    }


    public function getConnection(string $name = null): Client
    {
        if (!$name) $name = "default";

        if (!isset($this->connections[$name])) {

            if (!isset($this->config['connections']['default'])) throw new ConnectionNotFoundException($name);

            $config = $this->config['connections']['default'];

            $connection = new Client($config['uri'], $config['uriOptions']??[], $config['driverOptions']??[]);
            $this->connections[$name] = $connection;
        }

        return $this->connections[$name];
    }



}
