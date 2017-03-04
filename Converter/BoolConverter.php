<?php

namespace DGC\MongoODMBundle\Converter;

class BoolConverter extends AbstractConverter
{

    public function toDatabaseValue($value)
    {
        return $value !== null ? (boolean) $value : null;
    }

    public function toObjectValue($value, array $options)
    {
        return $value !== null ? (boolean) $value : null;
    }

}
