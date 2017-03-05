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

    protected $limit = null;
    protected $skip = null;
    protected $sort = [];
    protected $hydrate = true;

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

    public function toArray(): array
    {
        return [
            "query" => parent::toArray(),
            "limit" => $this->limit,
            "skip" => $this->skip,
            "sort" => $this->sort,
            "hydrate" => $this->hydrate
        ];
    }

    public function getQuery(): Query
    {
        return new Query($this->documentManager, $this->class, $this->toArray());
    }

    public function limit(int $limit): Expression
    {
        $this->limit = $limit;
        return $this;
    }

    public function skip(int $skip): Expression
    {
        $this->skip = $skip;
        return $this;
    }

    public function sort($sortArrayOrField, $order = null): Expression
    {
        if (!is_array($sortArrayOrField)) {
            $sortArrayOrField = [
                $sortArrayOrField => $order
            ];
        }

        foreach ($sortArrayOrField as $k=>$v) {
            if ($v == 'asc') $sortArrayOrField[$k] = 1;
            if ($v == 'desc') $sortArrayOrField[$k] = -1;
        }

        $this->sort = array_merge($this->sort, $sortArrayOrField);
        return $this;
    }

    public function hydrate(bool $hydrate = false): Expression
    {
        $this->hydrate = $hydrate;
        return $this;
    }

}