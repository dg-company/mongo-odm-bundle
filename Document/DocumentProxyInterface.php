<?php

namespace DGC\MongoODMBundle\Document;

interface DocumentProxyInterface
{

    public function __debugInfo(): array;
    public function _getData();

}
