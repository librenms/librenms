<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace Leaseweb\InfluxDB;

/**
 * Class Client
 *
 * @package Leaseweb\InfluxDB
 */
class Client
{
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
     * @var string
     */
    protected $database = '';

    /**
     * @var int
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
     * @param string $host
     * @param int    $port
     * @param string $username
     * @param string $password
     * @param string $database
     * @param bool   $ssl
     * @param bool   $verifySSL
     * @param int    $timeout
     *
     * @todo add UDP support
     * @todo add SSL support
     */
    public function __construct(
        $host,
        $port = 8086,
        $username = '',
        $password = '',
        $database = '',
        $ssl = false,
        $verifySSL = false,
        $timeout = 0
//        $useUdp = false,
//        $udpPort = 4444
    )
    {

        $this->host = $host;
        $this->port = (int) $port;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->timeout = $timeout;
        $this->verifySSL = (bool) $verifySSL;

        if ($ssl) {
            $this->scheme = 'https';
        }

        // the the base URI
        $this->setBaseURI(sprintf('%s://%s:%d', $this->scheme, $this->host, $this->port));

        $return = null;

        // return a database instance if a database name has been given
        if ($this->database) {
            $return = $this->db($database);
        }

        return $return;

    }

    /**
     * Use the given database
     *
     * @param string $name
     *
     * @return Database
     */
    public function db($name)
    {

        if (empty($name)) {
            throw new \InvalidArgumentException(sprintf('No database provided'));
        }

        return new Database($name, $this);
    }

    /**
     * Build the client from a dsn
     *
     * Example: tcp+influxdb://username:pass@localhost:8086/databasename', timeout=5
     *
     * @param string $dsn
     *
     * @todo finish this functionality
     */
    public static function fromDSN($dsn)
    {
        $args  = array();



    }

    /**
     * @return mixed
     */
    public function getBaseURI()
    {
        return $this->baseURI;
    }

    /**
     * @param mixed $baseURI
     */
    public function setBaseURI($baseURI)
    {
        $this->baseURI = $baseURI;
    }
}