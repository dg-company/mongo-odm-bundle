<?php

namespace DGC\MongoODMBundle\Converter;

use MongoDB\BSON\ObjectID;
use DGC\MongoODMBundle\Exception\InvalidFieldValueException;

class ObjectIdConverter extends AbstractConverter
{

    public function toDatabaseValue($value)
    {
        if ($value === null) return null;

        if (!$value instanceof ObjectID) throw new InvalidFieldValueException("Expected value of type MongoDB\\BSON\\ObjectID");

        return $value;
    }

    public function toObjectValue($value, array $options)
    {
        return $value;
    }

}
