<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace InfluxDB\Driver;

/**
 * Interface DriverInterface
 *
 * @package InfluxDB\Driver
 */
interface DriverInterface
{

    /**
     * Called by the client write() method, will pass an array of required parameters such as db name
     *
     * will contain the following parameters:
     *
     * [
     *  'database' => 'name of the database',
     *  'url' => 'URL to the resource',
     *  'method' => 'HTTP method used'
     * ]
     *
     * @param array $parameters
     *
     * @return mixed
     */
    public function setParameters(array $parameters);

    /**
     * @return array
     */
    public function getParameters();

    /**
     * Send the data
     *
     * @param $data
     *
     * @return mixed
     */
    public function write($data = null);

    /**
     * Should return if sending the data was successful
     *
     * @return bool
     */
    public function isSuccess();

}
