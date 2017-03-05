<?php

namespace DGC\MongoODMBundle\QueryBuilder;

use DGC\MongoODMBundle\Document\Document;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Regex;
use DGC\MongoODMBundle\Exception\NotScalarValueException;

class Field
{
    /** @var Expression */
    protected $expression;

    protected $query;

    public function __construct(Expression $expression)
    {
        $this->expression = $expression;
    }

    public function toArray()
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

    public function equals($value): Expression
    {
        $this->checkValue($value);

        $this->query = $value;

        return $this->expression;
    }

    protected function compare(string $operator, $value): Expression
    {
        $this->checkValue($value);

        $this->query = [
            $operator => $value
        ];

        return $this->expression;
    }

    public function notEqual($value): Expression
    {
        return $this->compare('$ne', $value);
    }

    public function gt($value): Expression
    {
        return $this->compare('$gt', $value);
    }

    public function lt($value): Expression
    {
        return $this->compare('$lt', $value);
    }

    public function gte($value): Expression
    {
        return $this->compare('$gte', $value);
    }

    public function lte($value): Expression
    {
        return $this->compare('$lte', $value);
    }

    public function in($value): Expression
    {
        return $this->compare('$in', $value);
    }

    public function notIn($value): Expression
    {
        return $this->compare('$nin', $value);
    }

    public function not(Expression $value): Expression
    {
        $this->query = [
            '$not' => $value
        ];

        return $this->expression;
    }

    public function exists(): Expression
    {
        $this->query = [
            '$exists' => true
        ];

        return $this->expression;
    }

    public function mod(int $divisor, int $remainder): Expression
    {
        $this->query = [
            '$mod' => [
                $divisor,
                $remainder
            ]
        ];

        return $this->expression;
    }

    public function text(string $search, string $language, bool $caseSensitive = false, bool $diacriticSensitive = false): Expression
    {
        $this->query = [
            '$text' => [
                '$search' => $search,
                '$language' => $language,
                '$caseSensitive' => $caseSensitive,
                '$diacriticSensitive' => $diacriticSensitive
            ]
        ];

        return $this->expression;
    }

    public function where(string $js): Expression
    {
        $this->query = [
            '$where' => $js
        ];

        return $this->expression;
    }

    public function size(int $size): Expression
    {
        $this->query = [
            '$size' => $size
        ];

        return $this->expression;
    }

}
