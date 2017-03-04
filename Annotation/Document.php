<?php

namespace DGC\MongoODMBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Document extends Annotation
{
    protected $document;

    public function getSettings(): array
    {
        return [
            "document" => $this->document
        ];
    }
}
