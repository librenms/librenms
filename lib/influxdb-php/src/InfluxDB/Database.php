<?php

namespace InfluxDB;

use InfluxDB\Database\Exception as DatabaseException;
use InfluxDB\Database\RetentionPolicy;
use InfluxDB\Query\Builder as QueryBuilder;

/**
 * Class Database
 *
 * @package InfluxDB
 * @author  Stephen "TheCodeAssassin" Hoogendijk
 */
class Database
{
    /**
     * The name of the Database
     *
     * @var string
     */
    protected $name = '';

    /**
     * @var Client
     */
    protected $client;

    /**
     * Precision constants
     */
    const PRECISION_NANOSECONDS = 'n';
    const PRECISION_MICROSECONDS = 'u';
    const PRECISION_MILLISECONDS = 'ms';
    const PRECISION_SECONDS = 's';
    const PRECISION_MINUTES = 'm';
    const PRECISION_HOURS = 'h';

    /**
     * Construct a database object
     *
     * @param string $name
     * @param Client $client
     */
    public function __construct($name, Client $client)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('No database name provided');
        }

        $this->name = (string) $name;
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Query influxDB
     *
     * @param  string $query
     * @param  array  $params
     * @return ResultSet
     * @throws Exception
     */
    public function query($query, $params = [])
    {
        return $this->client->query($this->name, $query, $params);
    }

    /**
     * Create this database
     *
     * @param  RetentionPolicy $retentionPolicy
     * @param bool             $createIfNotExists Only create the database if it does not yet exist
     *
     * @return ResultSet
     * @throws DatabaseException
     */
    public function create(RetentionPolicy $retentionPolicy = null, $createIfNotExists = true)
    {
        try {
            $query = sprintf(
                'CREATE DATABASE %s"%s"',
                ($createIfNotExists ? 'IF NOT EXISTS ' : ''),
                $this->name
            );

            $this->query($query);

            if ($retentionPolicy) {
                $this->createRetentionPolicy($retentionPolicy);
            }
        } catch (\Exception $e) {
            throw new DatabaseException(
                sprintf('Failed to created database %s', $this->name),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param  RetentionPolicy $retentionPolicy
     * @return ResultSet
     */
    public function createRetentionPolicy(RetentionPolicy $retentionPolicy)
    {
        return $this->query($this->getRetentionPolicyQuery('CREATE', $retentionPolicy));
    }

    /**
     * Writes points into InfluxDB
     *
     * @param  Point[] $points    Array of points
     * @param  string  $precision The timestamp precision (defaults to nanoseconds)
     * @return bool
     * @throws Exception
     */
    public function writePoints(array $points, $precision = self::PRECISION_NANOSECONDS)
    {
        $payload = array_map(
            function (Point $point) {
                return (string) $point;
            },
            $points
        );

        try {
            $parameters = [
                'url' => sprintf('write?db=%s&precision=%s', $this->name, $precision),
                'database' => $this->name,
                'method' => 'post'
            ];

            return $this->client->write($parameters, $payload);

        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @return bool
     */
    public function exists()
    {
        $databases = $this->client->listDatabases();

        return in_array($this->name, $databases);
    }

    /**
     * @param RetentionPolicy $retentionPolicy
     */
    public function alterRetentionPolicy(RetentionPolicy $retentionPolicy)
    {
        $this->query($this->getRetentionPolicyQuery('ALTER', $retentionPolicy));
    }

    /**
     * @return array
     * @throws Exception
     */
    public function listRetentionPolicies()
    {
        return $this->query(sprintf('SHOW RETENTION POLICIES ON "%s"', $this->name))->getPoints();
    }

    /**
     * Drop this database
     */
    public function drop()
    {
        $this->query(sprintf('DROP DATABASE "%s"', $this->name));
    }

    /**
     * Retrieve the query builder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return new QueryBuilder($this);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param  string          $method
     * @param  RetentionPolicy $retentionPolicy
     * @return string
     */
    protected function getRetentionPolicyQuery($method, RetentionPolicy $retentionPolicy)
    {
        $query = sprintf(
            '%s RETENTION POLICY "%s" ON "%s" DURATION %s REPLICATION %s',
            $method,
            $retentionPolicy->name,
            $this->name,
            $retentionPolicy->duration,
            $retentionPolicy->replication
        );

        if ($retentionPolicy->default) {
            $query .= " DEFAULT";
        }

        return $query;
    }
}
