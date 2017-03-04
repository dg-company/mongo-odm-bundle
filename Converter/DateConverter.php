<?php

namespace DGC\MongoODMBundle\Converter;

use DGC\MongoODMBundle\Exception\InvalidFieldValueException;
use MongoDB\BSON\UTCDateTime;

class DateConverter extends AbstractConverter
{

    public function toDatabaseValue($value)
    {
        if ($value === null) return null;

        if ($value instanceof \DateTime) {

            $ms  = $value->getTimestamp()*1000+$value->format('u');

            return new UTCDateTime($ms);

        } else {
            throw new InvalidFieldValueException("Expected value of type \\DateTime");
        }
    }

    public function toObjectValue($value, array $options)
    {
        if ($value === null) return null;

        if (!$value instanceof UTCDateTime) throw new InvalidFieldValueException("Expected value of type \\DateTime");

        return $value->toDateTime();
    }

}
