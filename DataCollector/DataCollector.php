<?php

namespace DGC\MongoODMBundle\DataCollector;

use MongoDB\BSON\Regex;
use MongoDB\Client;
use MongoDB\Database;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DataCollector extends \Symfony\Component\HttpKernel\DataCollector\DataCollector
{
    protected $debugToken;
    protected $debugTokenCounter = 0;

    /** @var Client[] */
    protected $connections = [];

    protected $data = [];

    protected $queries = [];

    public function __construct()
    {
        $this->debugToken = uniqid("odmDebug-");
    }

    public function getDebugToken(): string
    {
        $this->debugTokenCounter++;
        return $this->debugToken."-".$this->debugTokenCounter;
    }

    public function observeDatabaseConnection(Client $connection, string $connectionName, string $databaseName)
    {
        if (!isset($this->connections[$connectionName])) {
            $this->connections[$connectionName] = [
                'connection' => $connection,
                'databaseNames' => []
            ];
        }
        $this->connections[$connectionName]['databaseNames'][$databaseName] = true;
    }

    public function logQuery(string $command, array $parameters, float $time, string $debugToken, array $debugInfo)
    {
        $this->queries[] = [
            'token' => $debugToken,
            'command' => $command,
            'parameters' => $parameters,
            'time' => ceil($time/1000),
            'connection' => $debugInfo['connectionName'],
            'database' => $debugInfo['databaseName'],
            'profile' => null,
            'warnings' => []
        ];
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        //collect profiles from database
        $profiles = [];

        foreach ($this->connections as $connectionInfo) {

            /** @var Client $connection */
            $connection = $connectionInfo['connection'];

            foreach ($connectionInfo['databaseNames'] as $databaseName=>$x) {

                $logs = $connection->selectCollection($databaseName, "system.profile")->find([
                    'query.$comment' => new Regex('^'.$this->debugToken,'')
                ], [
                    'typeMap' => [
                        'root' => 'array',
                        'document' => 'array',
                        'array' => 'array'
                    ]
                ]);

                foreach ($logs as $log) {

                    $token = $log['query']['$comment'];

                    unset($log['query']['$comment']); //remove debug token

                    $profiles[$token] = $log;

                }

            }
        }

        $numberOfQueries = 0;
        $numberOfWarnings = 0;

        foreach ($this->queries as $queryKey=>$query) {

            $token = $query['token'];

            if (isset($profiles[$token])) {
                $this->queries[$queryKey]['profile'] = $profiles[$token];
            }

            $parameters = [];
            foreach ($query['parameters'] as $p) {
                if (empty($p)) continue;
                $parameters[] = json_encode($p);
            }

            $queryString = $query['database'].'.'.$query['command'].'('.implode(", ", $parameters).')';

            $queryString = preg_replace('@\{\"\$oid\"\:\"([a-f0-9]{24})\"\}@is', 'ObjectId("\1")', $queryString);

            $this->queries[$queryKey]['queryString'] = $queryString;

            $numberOfQueries++;

            //check for warnings
            //TODO: check index usage using profile
            /*
             * $this->queries[$queryKey]['warnings'][] = "No index used!";
             * $numberOfWarnings++;
             */

        }

        $this->data = [
            'numberOfQueries' => $numberOfQueries,
            'numberOfWarnings' => $numberOfWarnings,
            'queries' => $this->queries
        ];
    }

    public function getName()
    {
        return 'dgc_mongo_odm';
    }

    public function getData()
    {
        return $this->data;
    }

}