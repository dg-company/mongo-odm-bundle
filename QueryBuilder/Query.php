<?php

namespace DGC\MongoODMBundle\QueryBuilder;

use DGC\MongoODMBundle\Service\DocumentManager;

class Query
{
    protected $documentManager;
    protected $class;
    protected $query;
    protected $options = [];
    protected $hydrate = true;

    public function __construct(DocumentManager $documentManager, string $class, array $query)
    {
        $this->documentManager = $documentManager;
        $this->class = $class;
        $this->query = $query;
        $this->options = $this->getOptions($query);
        $this->hydrate = $query['hydrate']??true;
    }

    protected function getOptions(array $query): array
    {
        $options = [];
        if (isset($query['limit']) AND $query['limit'] > 0) $options['limit'] = intval($query['limit']);
        if (isset($query['skip']) AND $query['skip'] > 0) $options['skip'] = intval($query['skip']);
        if (isset($query['sort']) AND count($query['sort']) > 0) $options['sort'] = $query['sort'];
        return $options;
    }

    public function find(): array
    {
        if ($this->hydrate) {
            return $this->documentManager->find($this->class, $this->query['query'], $this->options);
        } else {
            return $this->documentManager->findRaw($this->class, $this->query['query'], $this->options);
        }
    }

    public function findOne()
    {
        if ($this->hydrate) {
            return $this->documentManager->findOne($this->class, $this->query['query'], $this->options);
        } else {
            return $this->documentManager->findOneRaw($this->class, $this->query['query'], $this->options);
        }
    }

    public function count()
    {
        return $this->documentManager->getCollectionForClass($this->class)->count($this->query['query']);
    }

}