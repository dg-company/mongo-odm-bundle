<?php

namespace DGC\MongoODMBundle\Annotation;

interface FieldAnnotationInterface
{
    public function getConverterName():string;
    public function getConverterSettings():array;
}
