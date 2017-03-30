<?php

namespace DGC\MongoODMBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use DGC\MongoODMBundle\Exception\UnknownTypeException;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Field extends Annotation implements FieldAnnotationInterface
{
    protected $type;

    public function getConverterName():string
    {
        switch ($this->type) {

            case 'float': return 'float'; break;
            case 'string': return 'string'; break;
            case 'object': return 'object'; break;
            case 'array': return 'array'; break;
            case 'binData': return 'binData'; break; //TODO
            case 'objectId': return 'objectId'; break;
            case 'bool': return 'bool'; break;
            case 'date': return 'date'; break;
            case 'int': return 'int'; break;
            case 'timestamp': return 'timestamp'; break; //TODO

            case 'raw': return 'raw'; break;

            default: throw new UnknownTypeException("Unknown field type ".$this->type);
        }
    }

    public function getConverterSettings():array
    {
        return [];
    }

}
