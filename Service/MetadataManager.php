<?php

namespace DGC\MongoODMBundle\Service;

use Doctrine\Common\Annotations\Reader;
use DGC\MongoODMBundle\Annotation\Document;
use DGC\MongoODMBundle\Annotation\EmbeddedDocument;
use DGC\MongoODMBundle\Annotation\FieldAnnotationInterface;
use DGC\MongoODMBundle\Exception\ClassNotFoundException;
use DGC\MongoODMBundle\Exception\MissingDocumentAnnotationException;
use DGC\MongoODMBundle\Exception\UnknownPropertyException;

class MetadataManager
{
    protected $annotationReader;
    protected $metadata = [];

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    protected function getMetadataForClass(string $class): array
    {
        if (isset($this->metadata[$class])) return $this->metadata[$class];

        if (!class_exists($class)) throw new ClassNotFoundException("Class not found: ".$class);

        $reflectionClass = new \ReflectionClass($class);

        //parse document settings
        /** @var Document $documentAnnotation */
        $documentAnnotation = $this->annotationReader->getClassAnnotation($reflectionClass, Document::class);

        if (!$documentAnnotation) {
            $documentAnnotation = $this->annotationReader->getClassAnnotation($reflectionClass, EmbeddedDocument::class);
            if (!$documentAnnotation) throw new MissingDocumentAnnotationException("Missing document annotation for ".$class);
        }

        $settings = $documentAnnotation->getSettings();

        //parse properties
        $properties = [];

        foreach ($reflectionClass->getProperties() as $property) {

            $annotations = $this->annotationReader->getPropertyAnnotations($property);

            foreach ($annotations as $annotation) {
                if ($annotation instanceof FieldAnnotationInterface) {

                    $properties[$property->getName()] = [
                        'converter' => $annotation->getConverterName(),
                        'converterSettings' => $annotation->getConverterSettings()
                    ];

                    break;
                }
            }

        }

        //get all getter functions
        $getter = [];

        foreach ($reflectionClass->getMethods() as $method) {
            if (preg_match('@^(is|get)(.*)$@is', $method->getName(), $matches)) {

                $getter[] = [
                    'name' => $method->getName(),
                    'propertyName' => lcfirst($matches[2]),
                    'returnType' => (String) $method->getReturnType(),
                    'returnTypeAllowsNull' => $method->getReturnType()->allowsNull(),
                    'returnTypeIsBuiltin' => $method->getReturnType()->isBuiltin()
                ];

            }
        }

        $this->metadata[$class] = [
            'settings' => $settings,
            'properties' => $properties,
            'getterFunctions' => $getter
        ];

        return $this->metadata[$class];
    }

    public function getConnectionName(string $class): ?string
    {
        $metadata = $this->getMetadataForClass($class);
        return $metadata['settings']['connection']??null;
    }

    public function getDatabaseName(string $class): ?string
    {
        $metadata = $this->getMetadataForClass($class);
        return $metadata['settings']['database']??null;
    }

    public function getCollectionName(string $class): ?string
    {
        $metadata = $this->getMetadataForClass($class);

        if (isset($metadata['settings']['collection'])) return $metadata['settings']['collection'];

        return substr($class, strrpos($class, '\\')+1);
    }

    public function getPropertyNames(string $class): array
    {
        $metadata = $this->getMetadataForClass($class);
        return array_keys($metadata['properties']);
    }

    public function getGetterFunctions(string $class): array
    {
        $metadata = $this->getMetadataForClass($class);
        return $metadata['getterFunctions'];
    }

    public function getConverterNameForProperty(string $class, string $propertyName): string
    {
        $metadata = $this->getMetadataForClass($class);
        if (isset($metadata['properties'][$propertyName])) {
            return $metadata['properties'][$propertyName]['converter'];
        } else throw new UnknownPropertyException("Unknown property ".$propertyName." of class ".$class);
    }

    public function getConverterSettingsForProperty(string $class, string $propertyName): array
    {
        $metadata = $this->getMetadataForClass($class);
        if (isset($metadata['properties'][$propertyName])) {
            return $metadata['properties'][$propertyName]['converterSettings'];
        } else {
            return [];
        }
    }

}
