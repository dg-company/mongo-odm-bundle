<?php

namespace DGC\MongoODMBundle\Converter;

use MongoDB\Model\BSONArray;
use DGC\MongoODMBundle\Document\Document;
use DGC\MongoODMBundle\Document\DocumentProxyInterface;
use DGC\MongoODMBundle\Exception\InvalidFieldValueException;
use DGC\MongoODMBundle\Service\MetadataManager;
use DGC\MongoODMBundle\Service\ProxyFactory;

class ReferenceManyConverter extends AbstractConverter
{

    protected $proxyFactory;
    protected $metadataManager;

    public function __construct(ProxyFactory $proxyFactory, MetadataManager $metadataManager)
    {
        $this->proxyFactory = $proxyFactory;
        $this->metadataManager = $metadataManager;
    }

    public function toDatabaseValue($value)
    {
        if ($value === null) return null;

        if (!is_array($value)) throw new InvalidFieldValueException("Expected value of type array");

        $references = [];

        /** @var Document $document */
        foreach ($value as $document) {
            if (!$document instanceof Document) throw new InvalidFieldValueException("Unable to persist reference to object which is no child of ".Document::class);

            if ($document instanceof DocumentProxyInterface) {
                $class = get_parent_class($document);
            } else {
                $class = get_class($document);
            }

            $references[] = [
                '$ref' => $this->metadataManager->getCollectionName($class),
                '$id' => $document->getId()
            ];
        }

        return $references;
    }

    public function toObjectValue($value, array $options)
    {
        if ($value === null || !is_array($value)) return null;

        $classes = [];

        foreach ($value as $ref) {

            $classes[] = $this->proxyFactory->getProxyForClass($options['document'], [
                '_id' => $ref['$id']
            ]);

        }

        return $classes;
    }

}
