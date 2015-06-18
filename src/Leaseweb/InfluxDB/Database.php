<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace Leaseweb\InfluxDB;

use GuzzleHttp\Client as httpClient;
use Leaseweb\InfluxDB\Database\RetentionPolicy;
use Leaseweb\InfluxDB\Query\Builder as QueryBuilder;

/**
 * Class Database
 *
 * @todo admin functionality
 *
 * @package Leaseweb\InfluxDB
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
     * @var httpClient
     */
    protected $httpClient;

    /**
     * Construct a database object
     *
     * @param string $name
     * @param Client $client
     */
    public function __construct($name, Client $client)
    {
        $this->client = $client;

        $this->httpClient = new Client(array('base_uri' => $this->client->getBaseURI()));
    }

    /**
     * Query influxDB
     *
     * @param string $query
     * @param array  $params
     */
    public function query($query, $params = array())
    {
        $params = array_merge(array('q' => $query, $params));

        $result = $this->httpClient->get('query', $params);

        die(var_dump($result));
    }

    /**
     * Create this database
     *
     * @param RetentionPolicy $retentionPolicy
     */
    public function create(RetentionPolicy $retentionPolicy)
    {
        return $this->query(sprintf('CREATE DATABASE %s', $this->name));
    }

    /**
     * Drop this database
     */
    public function drop()
    {

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


}