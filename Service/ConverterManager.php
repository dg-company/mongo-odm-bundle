<?php

namespace DGC\MongoODMBundle\Service;

use DGC\MongoODMBundle\Converter\AbstractConverter;
use Symfony\Component\DependencyInjection\Container;

class ConverterManager
{
    protected $serviceContainer;

    public function __construct(Container $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    public function get(string $name): AbstractConverter
    {
        return $this->serviceContainer->get("dgc_mongo_odm.converter.".$name);
    }

}
