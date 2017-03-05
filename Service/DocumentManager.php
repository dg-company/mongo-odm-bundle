<?php

namespace DGC\MongoODMBundle\Service;

use DGC\MongoODMBundle\Exception\NoDocumentException;
use DGC\MongoODMBundle\QueryBuilder\QueryBuilder;
use MongoDB\Collection;
use DGC\MongoODMBundle\Document\Document;
use DGC\MongoODMBundle\Document\DocumentProxyInterface;
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

    public function findOneRaw(string $class, array $query = [], array $options = []): ?array
    {
        return $this->getCollectionForClass($class)->findOne($query, array_merge($this->defaultQueryOptions, $options));
    }

    public function findRaw(string $class, array $query = [], array $options = []): ?array
    {
        /** @var array $result */
        $result = $this->getCollectionForClass($class)->find($query, array_merge($this->defaultQueryOptions, $options));
        return $result;
    }

    public function find(string $class = null, array $query = [], array $options = []): array
    {
        $result = $this->getCollectionForClass($class)->find($query, array_merge($this->defaultQueryOptions, $options));
        if (!$result) return null;

        $documents = [];

        foreach ($result as $item) {
            $documents[] = $this->proxyFactory->getProxyForClass($class, $item);
        }
        return $documents;
    }

    public function findOne(string $class = null, array $query, array $options = []): ?Document
    {
        $result = $this->getCollectionForClass($class)->findOne($query, array_merge($this->defaultQueryOptions, $options));
        if (!$result) return null;
        return $this->proxyFactory->getProxyForClass($class, $result);
    }

    /**
     * @param Document|Document[] $documents
     * @throws MissingIdException
     */
    public function save($documents)
    {
        if (!is_array($documents)) {
            $documents = [$documents];
        }

        foreach ($documents as $document) {

            if (!$document instanceof Document) throw new NoDocumentException("The provided object is no instance of ".Document::class);

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

    }

    public function createQueryBuilder(string $class): QueryBuilder
    {
        return new QueryBuilder($this, $class);
    }

}
