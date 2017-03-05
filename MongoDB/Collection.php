<?php

namespace DGC\MongoODMBundle\MongoDB;


use DGC\MongoODMBundle\DataCollector\DataCollector;
use MongoDB\Driver\Manager;

class Collection extends \MongoDB\Collection
{
    protected $dataCollector;
    protected $debug = false;
    protected $debugInfo = [];

    public function __construct(Manager $manager, $databaseName, $collectionName, array $options = [], string $connectionName, DataCollector $dataCollector = null)
    {
        if ($dataCollector) {
            $this->debug = true;
            $this->dataCollector = $dataCollector;
            $this->debugInfo = [
                'connectionName' => $connectionName,
                'databaseName' => $databaseName
            ];
        }

        parent::__construct($manager, $databaseName, $collectionName, $options);
    }

    public function _runWithProfiler(string $command, array $filter = null, array ...$parameters)
    {
        $debugToken = $this->dataCollector->getDebugToken();

        $time = microtime(true);

        $originalParameters = $parameters;
        $originalFilter = $filter;

        if ($filter !== null) {
            $filter['$comment'] = $debugToken;
            array_unshift($parameters, $filter);
            array_unshift($originalParameters, $originalFilter);
        }

        $result = call_user_func_array(array($this, 'parent::'.$command), $parameters);

        $this->dataCollector->logQuery($command, $originalParameters, microtime(true)-$time, $debugToken, $this->debugInfo);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function aggregate(array $pipeline, array $options = [])
    {
        if (!$this->debug) return parent::aggregate($pipeline, $options);

        return $this->_runWithProfiler("aggregate", null, $pipeline, $options);
    }

    /**
     * @inheritdoc
     */
    public function bulkWrite(array $operations, array $options = [])
    {
        if (!$this->debug) return parent::bulkWrite($operations, $options);

        return $this->_runWithProfiler("bulkWrite", null, $operations, $options);
    }

    /**
     * @inheritdoc
     */
    public function count($filter = [], array $options = [])
    {
        if (!$this->debug) return parent::count($filter, $options);

        return $this->_runWithProfiler("count", $filter, $options);
    }

    /**
     * @inheritdoc
     */
    public function createIndex($key, array $options = [])
    {
        if (!$this->debug) return parent::createIndex($key, $options);

        return $this->_runWithProfiler("createIndex", null, $key, $options);
    }

    /**
     * @inheritdoc
     */
    public function createIndexes(array $indexes, array $options = [])
    {
        if (!$this->debug) return parent::createIndexes($indexes, $options);

        return $this->_runWithProfiler("createIndexes", null, $indexes, $options);
    }

    /**
     * @inheritdoc
     */
    public function deleteMany($filter, array $options = [])
    {
        if (!$this->debug) return parent::deleteMany($filter, $options);

        return $this->_runWithProfiler("deleteMany", $filter, $options);
    }

    /**
     * @inheritdoc
     */
    public function deleteOne($filter, array $options = [])
    {
        if (!$this->debug) return parent::deleteOne($filter, $options);

        return $this->_runWithProfiler("deleteOne", $filter, $options);
    }

    /**
     * @inheritdoc
     */
    public function distinct($fieldName, $filter = [], array $options = [])
    {
        if (!$this->debug) return parent::distinct($fieldName, $filter, $options);

        return $this->_runWithProfiler("distinct", null, $fieldName, $filter, $options);
    }

    /**
     * @inheritdoc
     */
    public function drop(array $options = [])
    {
        if (!$this->debug) return parent::drop($options);

        return $this->_runWithProfiler("drop", null, $options);
    }

    /**
     * @inheritdoc
     */
    public function dropIndex($indexName, array $options = [])
    {
        if (!$this->debug) return parent::dropIndex($indexName, $options);

        return $this->_runWithProfiler("dropIndex", null, $indexName, $options);
    }

    /**
     * @inheritdoc
     */
    public function dropIndexes(array $options = [])
    {
        if (!$this->debug) return parent::dropIndexes($options);

        return $this->_runWithProfiler("dropIndexes", null, $options);
    }

    /**
     * @inheritdoc
     */
    public function find($filter = [], array $options = [])
    {
        if (!$this->debug) return parent::find($filter, $options);

        return $this->_runWithProfiler("find", $filter, $options);
    }

    /**
     * @inheritdoc
     */
    public function findOne($filter = [], array $options = [])
    {
        if (!$this->debug) return parent::findOne($filter, $options);

        return $this->_runWithProfiler("findOne", $filter, $options);
    }

    /**
     * @inheritdoc
     */
    public function findOneAndDelete($filter, array $options = [])
    {
        if (!$this->debug) return parent::findOneAndDelete($filter, $options);

        return $this->_runWithProfiler("findOneAndDelete", $filter, $options);
    }

    /**
     * @inheritdoc
     */
    public function findOneAndReplace($filter, $replacement, array $options = [])
    {
        if (!$this->debug) return parent::findOneAndReplace($filter, $replacement, $options);

        return $this->_runWithProfiler("findOneAndReplace", $filter, $replacement, $options);
    }

    /**
     * @inheritdoc
     */
    public function findOneAndUpdate($filter, $update, array $options = [])
    {
        if (!$this->debug) return parent::findOneAndUpdate($filter, $update, $options);

        return $this->_runWithProfiler("findOneAndUpdate", $filter, $update, $options);
    }

    /**
     * @inheritdoc
     */
    public function insertMany(array $documents, array $options = [])
    {
        if (!$this->debug) return parent::insertMany($documents, $options);

        return $this->_runWithProfiler("insertMany", null, $documents, $options);
    }

    /**
     * @inheritdoc
     */
    public function insertOne($document, array $options = [])
    {
        if (!$this->debug) return parent::insertOne($document, $options);

        return $this->_runWithProfiler("insertOne", null, $document, $options);
    }

    /**
     * @inheritdoc
     */
    public function listIndexes(array $options = [])
    {
        if (!$this->debug) return parent::listIndexes($options);

        return $this->_runWithProfiler("listIndexes", null, $options);
    }

    /**
     * @inheritdoc
     */
    public function replaceOne($filter, $replacement, array $options = [])
    {
        if (!$this->debug) return parent::replaceOne($filter, $replacement, $options);

        return $this->_runWithProfiler("replaceOne", $filter, $replacement, $options);
    }

    /**
     * @inheritdoc
     */
    public function updateMany($filter, $update, array $options = [])
    {
        if (!$this->debug) return parent::updateMany($filter, $update, $options);

        return $this->_runWithProfiler("updateMany", $filter, $update, $options);
    }

    /**
     * @inheritdoc
     */
    public function updateOne($filter, $update, array $options = [])
    {
        if (!$this->debug) return parent::updateOne($filter, $update, $options);

        return $this->_runWithProfiler("updateOne", $filter, $update, $options);
    }

}