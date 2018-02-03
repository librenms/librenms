<?php

namespace InfluxDB;

use InfluxDB\Client\Admin;
use InfluxDB\Client\Exception as ClientException;
use InfluxDB\Driver\DriverInterface;
use InfluxDB\Driver\Exception as DriverException;
use InfluxDB\Driver\Guzzle;
use InfluxDB\Driver\QueryDriverInterface;
use InfluxDB\Driver\UDP;

/**
 * Class Client
 *
 * @package InfluxDB
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */
class Client
{
    /**
     * @var Admin
     */
    public $admin;

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var int
     */
    protected $port = 8086;

    /**
     * @var string
     */
    protected $username = '';

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var float
     */
    protected $timeout = 0;

    /**
     * @var bool
     */
    protected $scheme = 'http';

    /**
     * @var bool
     */
    protected $verifySSL = false;

    /**
     * @var bool
     */
    protected $useUdp = false;

    /**
     * @var int
     */
    protected $udpPort = 4444;

    /**
     * @var
     */
    protected $baseURI;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var DriverInterface
     */
    protected $driver;


    /**
     * Stores the last query that ran
     *
     * @var null
     */
    public static $lastQuery = null;

    /**
     * @param string $host
     * @param int    $port
     * @param string $username
     * @param string $password
     * @param bool   $ssl
     * @param bool   $verifySSL
     * @param int    $timeout
     */
    public function __construct(
        $host,
        $port = 8086,
        $username = '',
        $password = '',
        $ssl = false,
        $verifySSL = false,
        $timeout = 0
    ) {
        $this->host = (string) $host;
        $this->port = (int) $port;
        $this->username = (string) $username;
        $this->password = (string) $password;
        $this->timeout = (float) $timeout;
        $this->verifySSL = (bool) $verifySSL;

        if ($ssl) {
            $this->scheme = 'https';
            $this->options['verify'] = $verifySSL;
        }

        // the the base URI
        $this->baseURI = sprintf('%s://%s:%d', $this->scheme, $this->host, $this->port);

        // delay driver instantiation until it's actually needed
        $this->driver = null;

        $this->admin = new Admin($this);
    }

    /**
     * Use the given database
     *
     * @param  string $name
     * @return Database
     */
    public function selectDB($name)
    {
        return new Database($name, $this);
    }

    /**
     * Query influxDB
     *
     * @param  string $database
     * @param  string $query
     * @param  array  $parameters
     *
     * @return ResultSet
     * @throws Exception
     */
    public function query($database, $query, $parameters = [])
    {
        $driver = $this->getDriver();

        if (!$driver instanceof QueryDriverInterface) {
            throw new Exception('The currently configured driver does not support query operations');
        }

        if ($database) {
            $parameters['db'] = $database;
        }

        $parameters = [
            'url' => 'query?' . http_build_query(array_merge(['q' => $query], $parameters)),
            'database' => $database,
            'method' => 'get'
        ];

        // add authentication to the driver if needed
        if (!empty($this->username) && !empty($this->password)) {
            $parameters += ['auth' => [$this->username, $this->password]];
        }

        $driver->setParameters($parameters);

        try {
            // store the last query sent
            static::$lastQuery = $query;

            // perform the query and return the resultset
            return $driver->query();

        } catch (DriverException $e) {
            throw new Exception('Query has failed', $e->getCode(), $e);
        }
    }

    /**
     * Write data
     *
     * @param array        $parameters
     * @param string|array $payload     InfluxDB payload (Or array of payloads) that conform to the Line syntax.
     *
     * @return bool
     */
    public function write(array $parameters, $payload)
    {
        // retrieve the driver
        $driver = $this->getDriver();

        // add authentication to the driver if needed
        if (!empty($this->username) && !empty($this->password)) {
            $parameters += ['auth' => [$this->username, $this->password]];
        }

        // set the given parameters
        $driver->setParameters($parameters);

        // send the points to influxDB
        if (is_array($payload)) {
            $driver->write(implode("\n", $payload));
        } else {
            $driver->write($payload);
        }

        return $driver->isSuccess();
    }

    /**
     * List all the databases
     */
    public function listDatabases()
    {
        $result = $this->query(null, 'SHOW DATABASES')->getPoints();

        return $this->pointsToArray($result);
    }

    /**
     * List all the users
     *
     * @return array
     * @throws Exception
     */
    public function listUsers()
    {
        $result = $this->query(null, 'SHOW USERS')->getColumns();

        return (array) $result;
    }

    /**
     * Build the client from a dsn
     * Examples:
     *
     * https+influxdb://username:pass@localhost:8086/databasename
     * udp+influxdb://username:pass@localhost:4444/databasename
     *
     * @param  string $dsn
     * @param  int    $timeout
     * @param  bool   $verifySSL
     *
     * @return Client|Database
     * @throws ClientException
     */
    public static function fromDSN($dsn, $timeout = 0, $verifySSL = false)
    {
        $connParams = parse_url($dsn);
        $schemeInfo = explode('+', $connParams['scheme']);
        $dbName = null;
        $modifier = null;
        $scheme = $schemeInfo[0];

        if (isset($schemeInfo[1])) {
            $modifier = strtolower($schemeInfo[0]);
            $scheme = $schemeInfo[1];
        }

        if ($scheme != 'influxdb') {
            throw new ClientException($scheme . ' is not a valid scheme');
        }

        $ssl = $modifier === 'https' ? true : false;
        $dbName = isset($connParams['path']) ? substr($connParams['path'], 1) : null;

        $client = new self(
            $connParams['host'],
            $connParams['port'],
            isset($connParams['user']) ? $connParams['user'] : '',
            isset($connParams['pass']) ? $connParams['pass'] : '',
            $ssl,
            $verifySSL,
            $timeout
        );

        // set the UDP driver when the DSN specifies UDP
        if ($modifier == 'udp') {
            $client->setDriver(new UDP($connParams['host'], $connParams['port']));
        }

        return ($dbName ? $client->selectDB($dbName) : $client);
    }

    /**
     * @return mixed
     */
    public function getBaseURI()
    {
        return $this->baseURI;
    }

    /**
     * @return float
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @return bool
     */
    public function getVerifySSL()
    {
        return $this->verifySSL;
    }

    /**
     * @param Driver\DriverInterface $driver
     */
    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return DriverInterface|QueryDriverInterface
     */
    public function getDriver()
    {
        if ($this->driver !== null) {
            return $this->driver;
        }

        // set the default driver to guzzle
        $this->driver = new Guzzle(
            new \GuzzleHttp\Client(
                [
                    'timeout' => $this->timeout,
                    'base_uri' => $this->baseURI,
                    'verify' => $this->verifySSL
                ]
            )
        );

        return $this->driver;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Returns the last executed query
     *
     * @return null|string
     */
    public function getLastQuery()
    {
        return static::$lastQuery;
    }

    /**
     * @param  Point[] $points
     * @return array
     */
    protected function pointsToArray(array $points)
    {
        $names = [];

        foreach ($points as $item) {
            $names[] = $item['name'];
        }

        return $names;
    }

}
