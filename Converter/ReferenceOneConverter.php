<?php

namespace DGC\MongoODMBundle\Converter;

use DGC\MongoODMBundle\Document\Document;
use DGC\MongoODMBundle\Document\DocumentProxyInterface;
use DGC\MongoODMBundle\Exception\InvalidFieldValueException;
use DGC\MongoODMBundle\Service\MetadataManager;
use DGC\MongoODMBundle\Service\ProxyFactory;

class ReferenceOneConverter extends AbstractConverter
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

        if (!$value instanceof Document) throw new InvalidFieldValueException("Unable to persist reference to object which is no child of ".Document::class);

        if ($value instanceof DocumentProxyInterface) {
            $class = get_parent_class($value);
        } else {
            $class = get_class($value);
        }

        return [
            '$ref' => $this->metadataManager->getCollectionName($class),
            '$id' => $value->getId()
        ];
    }

    public function toObjectValue($value, array $options)
    {
        if ($value === null || !is_array($value)) return null;

        return $this->proxyFactory->getProxyForClass($options['document'], [
            '_id' => $value['$id']
        ]);
    }

}
