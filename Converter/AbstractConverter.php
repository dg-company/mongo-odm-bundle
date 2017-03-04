<?php

namespace DGC\MongoODMBundle\Converter;

abstract class AbstractConverter
{
    abstract public function toDatabaseValue($value);
    abstract public function toObjectValue($value, array $options);
}
