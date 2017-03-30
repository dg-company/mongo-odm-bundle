<?php

namespace DGC\MongoODMBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class EmbedMany extends Annotation implements FieldAnnotationInterface
{
    protected $document;

    public function getConverterName():string
    {
        return "embed_many";
    }

    public function getConverterSettings():array
    {
        return [
            "document" => $this->document
        ];
    }
}
