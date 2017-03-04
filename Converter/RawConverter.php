<?php

namespace DGC\MongoODMBundle\Converter;

class RawConverter extends AbstractConverter
{

    public function toDatabaseValue($value)
    {
        return $value;
    }

    public function toObjectValue($value, array $options)
    {
        return $value;
    }

}
