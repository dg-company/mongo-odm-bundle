<?php

namespace DGC\MongoODMBundle\Converter;

use DGC\MongoODMBundle\Document\Document;
use DGC\MongoODMBundle\Document\DocumentProxyInterface;
use DGC\MongoODMBundle\Exception\InvalidFieldValueException;
use DGC\MongoODMBundle\Service\MetadataManager;
use DGC\MongoODMBundle\Service\ProxyFactory;

class EmbedOneConverter extends AbstractConverter
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

        if (!$value instanceof Document) throw new InvalidFieldValueException("Unable to embed object which is no child of ".Document::class);

        if (!$value instanceof DocumentProxyInterface) {
            $value = $this->proxyFactory->getProxyForDocument($value);
        }

        $data = $value->_getData();

        return $data;
    }

    public function toObjectValue($value, array $options)
    {
        if ($value === null || !is_array($value)) return null;

        return $this->proxyFactory->getProxyForClass($options['document'], $value);
    }

}
