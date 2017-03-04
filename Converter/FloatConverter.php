<?php

namespace DGC\MongoODMBundle\Converter;

class FloatConverter extends AbstractConverter
{

    public function toDatabaseValue($value)
    {
        return $value !== null ? (float) $value : null;
    }

    public function toObjectValue($value, array $options)
    {
        return $value !== null ? (float) $value : null;
    }

}
