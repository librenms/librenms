<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace Leaseweb\InfluxDB;


class Client
{

    /**
     * @param string $host
     * @param int    $port
     * @param string $username
     * @param string $password
     * @param string $database
     * @param bool   $ssl
     * @param bool   $verifySSL
     * @param int    $timeout
     * @param bool   $useUdp
     * @param int    $udpPort
     */
    public function __construct($host,
        $port = 8086,
        $username = '',
        $password = '',
        $database = '',
        $ssl = false,
        $verifySSL = false,
        $timeout = 0,
        $useUdp = false,
        $udpPort = 4444)
    {

    }
}