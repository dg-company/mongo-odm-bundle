<?php

namespace DGC\MongoODMBundle\QueryBuilder;

use DGC\MongoODMBundle\Document\Document;
use DGC\MongoODMBundle\Service\DocumentManager;

class Query
{
    protected $documentManager;
    protected $class;
    protected $query;

    public function __construct(DocumentManager $documentManager, string $class, array $query)
    {
        $this->documentManager = $documentManager;
        $this->class = $class;
        $this->query = $query;
    }

    public function find(): array
    {
        return $this->documentManager->find($this->class, $this->query);
    }

    public function findOne(): ?Document
    {
        return $this->documentManager->findOne($this->class, $this->query);
    }

    public function count()
    {
        return $this->documentManager->getCollectionForClass($this->class)->count($this->query);
    }

}