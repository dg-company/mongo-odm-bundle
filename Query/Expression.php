<?php

namespace DGC\MongoODMBundle\QueryBuilder;

use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Regex;
use DGC\MongoODMBundle\Exception\NotScalarValueException;

class Expression
{
    /** @var QueryBuilder */
    protected $queryBuilder;

    protected $fieldName;
    protected $query;


    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function field(string $fieldName): Expression
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    public function toArray(): array
    {
        return $this->query;
    }

    protected function checkValue($value)
    {
        if (
            ! is_scalar($value) AND
            ! $value instanceof Regex AND
            ! $value instanceof ObjectID
        ) throw new NotScalarValueException();
    }

    public function equals($value): QueryBuilder
    {
        $this->checkValue($value);

        $this->query = [
            $this->fieldName => $value
        ];

        return $this->queryBuilder;
    }

    protected function compare(string $operator, $value): QueryBuilder
    {
        $this->checkValue($value);

        $this->query = [
            $this->fieldName => [
                $operator => $value
            ]
        ];

        return $this->queryBuilder;
    }

    public function notEqual($value): QueryBuilder
    {
        return $this->compare('$ne', $value);
    }

    public function gt($value): QueryBuilder
    {
        return $this->compare('$gt', $value);
    }

    public function lt($value): QueryBuilder
    {
        return $this->compare('$lt', $value);
    }

    public function gte($value): QueryBuilder
    {
        return $this->compare('$gte', $value);
    }

    public function lte($value): QueryBuilder
    {
        return $this->compare('$lte', $value);
    }



}