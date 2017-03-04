<?php

namespace DGC\MongoODMBundle\Converter;

class StringConverter extends AbstractConverter
{

    public function toDatabaseValue($value)
    {
        if ($value === null) return null;
        return (String) $value;
    }

    public function toObjectValue($value, array $options)
    {
        if ($value === null) return null;
        return (String) $value;
    }

}
