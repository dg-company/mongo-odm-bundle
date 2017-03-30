<?php

namespace DGC\MongoODMBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class EmbeddedDocument extends Annotation
{
    public function getSettings(): array
    {
        return [

        ];
    }
}
