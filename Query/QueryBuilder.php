<?php

namespace DGC\MongoODMBundle\Query;

use MongoDB\Collection;
use DGC\MongoODMBundle\Exception\UnknownOperationException;
use DGC\MongoODMBundle\Service\DocumentManager;
use DGC\MongoODMBundle\Service\DocumentProxyManager;

class QueryBuilder
{

    protected $documentManager;
    protected $documentProxyManager;

    protected $collection;
    protected $class;
    protected $operation = self::OPERATION_FIND;

    const OPERATION_FIND = "find";
    const OPERATION_FINDONE = "findone";

    /** @var Expression[] */
    protected $expressions = [];

    public function __construct(DocumentManager $documentManager, DocumentProxyManager $documentProxyManager, $class, string $operation)
    {
        $this->documentManager = $documentManager;
        $this->documentProxyManager = $documentProxyManager;

        $this->class = $class;
        $this->operation = $operation;

        $this->collection = $documentManager->getCollectionForClass($class);
    }

    public function field(string $fieldName): Expression
    {
        $expression = new Expression($this);
        $this->expressions[] = $expression;
        return $expression->field($fieldName);
    }

    public function getQuery(): array
    {
        $query = [];
        foreach ($this->expressions as $expression) {
            $expression = $expression->toArray();
            foreach ($expression as $k=>$v) {
                $query[$k] = $v;
            }
        }
        return $query;
    }

    public function execute()
    {
        if ($this->operation == self::OPERATION_FIND) {

            $documents = [];
            $cursor = $this->collection->find($this->getQuery());
            foreach ($cursor as $data) {
                $documents[] = $this->documentProxyManager->getProxyForClass($this->class, $data);
            }
            return $documents;

        } elseif ($this->operation == self::OPERATION_FINDONE) {

            $data = $this->collection->findOne($this->getQuery());
            if ($data === null) return $data;
            return $this->documentProxyManager->getProxyForClass($this->class, $data);

        } else throw new UnknownOperationException();
    }

}