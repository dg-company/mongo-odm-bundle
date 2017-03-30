<?php

namespace DGC\MongoODMBundle\Document;

use MongoDB\BSON\ObjectID;

abstract class Document
{

    public function __construct()
    {
        if (property_exists($this, "id") AND $this->id === null) {
            $this->id = new ObjectID();
        }
    }

}