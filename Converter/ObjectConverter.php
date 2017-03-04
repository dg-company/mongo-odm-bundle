<?php

namespace DGC\MongoODMBundle\Converter;

use DGC\MongoODMBundle\Exception\InvalidFieldValueException;

class ObjectConverter extends AbstractConverter
{

    public function toDatabaseValue($value)
    {
        if ($value === null) return null;

        if (!is_array($value)) throw new InvalidFieldValueException("Expected value of type array");

        return (array) $value;
    }

    public function toObjectValue($value, array $options)
    {
        return $value !== null ? (array) $value : null;
    }

}
