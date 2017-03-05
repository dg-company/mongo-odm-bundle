<?php

namespace DGC\MongoODMBundle\Service;

use DGC\MongoODMBundle\QueryBuilder\QueryBuilder;
use MongoDB\Collection;
use DGC\MongoODMBundle\Document\Document;
use DGC\MongoODMBundle\Document\DocumentProxyInterface;
use DGC\MongoODMBundle\Exception\ClassNotFoundException;
use DGC\MongoODMBundle\Exception\MissingIdException;

class DocumentManager
{
    protected $connectionManager;
    protected $proxyFactory;
    protected $metadataManager;
    protected $config;

    protected $defaultQueryOptions = [
        'typeMap' => [
            'root' => 'array',
            'document' => 'array',
            'array' => 'array'
        ]
    ];

    public function __construct(ConnectionManager $connectionManager, ProxyFactory $proxyFactory, MetadataManager $metadataManager, array $config)
    {
        $this->connectionManager = $connectionManager;
        $this->proxyFactory = $proxyFactory;
        $this->metadataManager = $metadataManager;
        $this->config = $config;
    }

    public function getCollectionForClass(string $class): Collection
    {
        //get connection
        $connectionName = $this->metadataManager->getConnectionName($class) ?? "default";

        $connection = $this->connectionManager->getConnection($connectionName);

        //get database
        $databaseName = $this->metadataManager->getDatabaseName($class) ?? $this->config['connections'][$connectionName]['default_database'] ?? $this->config['default_database'];

        //get collection
        $collectionName = $this->metadataManager->getCollectionName($class);

        return $connection->selectDatabase($databaseName)->selectCollection($collectionName);
    }

    /**
     * @param string $class
     * @param $id
     * @return array|null
     * @internal
     */
    public function findRawById(string $class, $id): ?array
    {
        $result = $this->getCollectionForClass($class)->findOne([
            '_id' => $id
        ], $this->defaultQueryOptions);
        //TODO: throw exception if not found
        return $result;
    }

    public function find(string $class = null, array $query = []): array
    {
        $result = $this->getCollectionForClass($class)->find($query, $this->defaultQueryOptions);
        if (!$result) return null;

        $documents = [];

        foreach ($result as $item) {
            $documents[] = $this->proxyFactory->getProxyForClass($class, $item);
        }
        return $documents;
    }

    public function findOne(string $class = null, array $query): ?Document
    {
        $result = $this->getCollectionForClass($class)->findOne($query, $this->defaultQueryOptions);
        if (!$result) return null;
        return $this->proxyFactory->getProxyForClass($class, $result);
    }

    public function save(Document $document)
    {
        //get proxy for document
        if (!$document instanceof DocumentProxyInterface) {
            $document = $this->proxyFactory->getProxyForDocument($document);
        }

        $data = $document->_getData();

        if (!isset($data['_id']) OR $data['_id'] === null) throw new MissingIdException();

        $updateData = $data;
        unset($updateData['_id']);

        $collection = $this->getCollectionForClass(get_parent_class($document));

        $collection->replaceOne([
            '_id' => $data['_id']
        ], $updateData, [
            'upsert' => true
        ]);
    }

    public function createQueryBuilder(string $class): QueryBuilder
    {
        return new QueryBuilder($this, $class);
    }

}
