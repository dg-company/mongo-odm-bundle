<?php

namespace DGC\MongoODMBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class EmbedOne extends Annotation implements FieldAnnotationInterface
{
    protected $document;

    public function getConverterName():string
    {
        return "embed_one";
    }

    public function getConverterSettings():array
    {
        return [
            "document" => $this->document
        ];
    }
}
