<?php

namespace DGC\MongoODMBundle\QueryBuilder;

use DGC\MongoODMBundle\Service\DocumentManager;

class QueryBuilder extends Expression
{

    protected $documentManager;

    protected $collection;
    protected $class;

    /** @var Expression[] */
    protected $expressions = [];

    public function __construct(DocumentManager $documentManager, $class)
    {
        $this->documentManager = $documentManager;

        $this->class = $class;

        $this->collection = $documentManager->getCollectionForClass($class);
    }

    public function expr(): Expression
    {
        return new Expression();
    }

    public function getQuery(): Query
    {
        return new Query($this->documentManager, $this->class, $this->toArray());
    }

}