<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace InfluxDB;

use InfluxDB\Database\RetentionPolicy;
use InfluxDB\Query\Builder as QueryBuilder;
use InfluxDB\Database\Exception as DatabaseException;

/**
 * Class Database
 *
 * @todo admin functionality
 *
 * @package InfluxDB
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
     *
     * @throws DatabaseException
     */
    public function __construct($name, Client $client)
    {
        $this->client = $client;

        if (!$name) {
            throw new \InvalidArgumentException('No database name provided');
        }

        $this->name = $name;

    }

    /**
     * @return string db name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Query influxDB
     *
     * @param string $query
     * @param array $params
     *
     * @return ResultSet
     *
     * @throws Exception
     */
    public function query($query, $params = array())
    {
        return $this->client->query($this->name, $query, $params);
    }

    /**
     * Create this database
     *
     * @param RetentionPolicy $retentionPolicy
     *
     * @return ResultSet
     *
     * @throws DatabaseException
     * @throws Exception
     */
    public function create(RetentionPolicy $retentionPolicy = null)
    {
        try {
            $this->query(sprintf('CREATE DATABASE %s', $this->name));

            if ($retentionPolicy) {
                $this->createRetentionPolicy($retentionPolicy);
            }

        } catch (\Exception $e) {
            throw new DatabaseException(
                sprintf('Failed to created database %s, exception: %s', $this->name, $e->getMessage())
            );
        }
    }

    /**
     * @param RetentionPolicy $retentionPolicy
     *
     * @return ResultSet
     */
    public function createRetentionPolicy(RetentionPolicy $retentionPolicy)
    {
        return $this->query($this->getRetentionPolicyQuery('CREATE', $retentionPolicy));
    }

    /**
     * Writes points into InfluxDB
     *
     * @param Point[]  $points    Array of points
     * @param string   $precision The timestamp precision (defaults to nanoseconds)
     *
     * @return bool
     * @throws Exception
     */
    public function writePoints(array $points, $precision = self::PRECISION_NANOSECONDS)
    {
        $payload = array();

        foreach ($points as $point) {

            if (!$point instanceof Point) {
                throw new \InvalidArgumentException('An array of Point[] should be passed');
            }

            $payload[] = (string) $point;
        }

        return $this->client->write($this->name, implode(PHP_EOL, $payload), $precision);
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
     *
     * @throws Exception
     */
    public function listRetentionPolicies()
    {
        return $this->query(sprintf('SHOW RETENTION POLICIES %s', $this->name))->getPoints();
    }


    /**
     * Drop this database
     */
    public function drop()
    {
        $this->query(sprintf('DROP DATABASE %s', $this->name));
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
     * @param                 $method
     * @param RetentionPolicy $retentionPolicy
     *
     * @return string
     */
    protected function getRetentionPolicyQuery($method, RetentionPolicy $retentionPolicy)
    {

        if (!in_array($method, array('CREATE', 'ALTER'))) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid method'));
        }

        $query = sprintf(
            '%s RETENTION POLICY %s ON %s DURATION %s REPLICATION %s',
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