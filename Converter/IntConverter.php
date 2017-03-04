<?php

namespace DGC\MongoODMBundle\Converter;

class IntConverter extends AbstractConverter
{

    public function toDatabaseValue($value)
    {
        return $value !== null ? (integer) $value : null;
    }

    public function toObjectValue($value, array $options)
    {
        return $value !== null ? (integer) $value : null;
    }

}
