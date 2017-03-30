<?php

namespace DGC\MongoODMBundle\Converter;

use DGC\MongoODMBundle\Document\DocumentProxyInterface;
use DGC\MongoODMBundle\Document\EmbeddedDocument;
use DGC\MongoODMBundle\Exception\InvalidFieldValueException;
use DGC\MongoODMBundle\Service\MetadataManager;
use DGC\MongoODMBundle\Service\ProxyFactory;

class EmbedManyConverter extends AbstractConverter
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

        $documents = [];

        /** @var EmbeddedDocument $document */
        foreach ($value as $document) {
            if (!$document instanceof EmbeddedDocument) throw new InvalidFieldValueException("Unable to persist embedded document which is no child of ".EmbeddedDocument::class);

            if (!$document instanceof DocumentProxyInterface) {
                $document = $this->proxyFactory->getProxyForDocument($document);
            }

            $documents[] = $document->_getData();

        }

        return $documents;
    }

    public function toObjectValue($value, array $options)
    {
        if ($value === null || !is_array($value)) return null;

        $classes = [];

        foreach ($value as $data) {

            $classes[] = $this->proxyFactory->getProxyForClass($options['document'], $data);

        }

        return $classes;
    }

}
