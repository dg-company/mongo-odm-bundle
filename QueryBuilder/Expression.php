<?php

namespace DGC\MongoODMBundle\QueryBuilder;

use DGC\MongoODMBundle\Exception\QueryBuilderException;

class Expression
{
    protected $fieldName;
    protected $query;

    public static function create()
    {
        return new self();
    }

    public function field(string $fieldName): Field
    {
        if ($fieldName == 'id') $fieldName = '_id';

        $field = new Field($this);
        $this->query[$fieldName] = $field;
        return $field;
    }

    public function toArray(): array
    {
        $data = [];
        /**
         * @var string $k
         * @var Field|Expression $v
         */
        foreach ($this->query as $k=>$v) {
            if ($v instanceof Field OR $v instanceof Expression) {
                $v = $v->toArray();
            } elseif (is_array($v)) {
                $sub = [];
                foreach ($v as $k2=>$v2) {
                    if ($v2 instanceof Field OR $v2 instanceof Expression) {
                        $v2 = $v2->toArray();
                    }
                    $sub[$k2] = $v2;
                }
                $v = $sub;
            }
            $data[$k] = $v;
        }
        return $data;
    }

    public function addAnd(Expression $expression): self
    {
        if (!isset($this->query['$and'])) {
            $this->query['$and'] = [];
        }
        $this->query['$and'][] = $expression;
        return $this;
    }

    public function addOr(Expression $expression): self
    {
        if (!isset($this->query['$or'])) {
            $this->query['$or'] = [];
        }
        $this->query['$or'][] = $expression;
        return $this;
    }

    public function addNor(Expression $expression): self
    {
        if (!isset($this->query['$nor'])) {
            $this->query['$nor'] = [];
        }
        $this->query['$nor'][] = $expression;
        return $this;
    }

    public function getQuery(): Query
    {
        throw new QueryBuilderException("getQuery() has to be called on QueryBuilder");
    }

    public function limit(int $limit): Expression
    {
        throw new QueryBuilderException("limit() has to be called on QueryBuilder");
    }

    public function skip(int $skip): Expression
    {
        throw new QueryBuilderException("skip() has to be called on QueryBuilder");
    }

    public function sort($sortArrayOrField, $order = null): Expression
    {
        throw new QueryBuilderException("sort() has to be called on QueryBuilder");
    }

    public function hydrate(bool $hydrate = false): Expression
    {
        throw new QueryBuilderException("sort() has to be called on QueryBuilder");
    }

}
