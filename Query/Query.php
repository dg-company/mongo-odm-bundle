<?php

namespace DGC\MongoODMBundle\Query;

use MongoDB\Collection;
use DGC\MongoODMBundle\Exception\UnknownOperationException;
use DGC\MongoODMBundle\Service\DocumentManager;
use DGC\MongoODMBundle\Service\DocumentProxyManager;

class Query
{

    const OPERATION_FIND = "find";
    const OPERATION_FINDONE = "findone";

    private $collection;
    private $operation;
    private $expressions = [];



}