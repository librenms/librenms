<?php
namespace InfluxDB\Adapter;

/**
 * The Adapter implement this interface if it supports database query
 */
interface QueryableInterface
{
    /**
     * Make query into database
     * @param string $query
     * @param string|bool $timePrecision
     */
    public function query($query, $timePrecision = false);

    /**
     * Return database
     */
    public function getDatabases();

    /**
     * Create database
     * @param string $name
     * @return array
     */
    public function createDatabase($name);

    /**
     * Delete database by database
     * @param string $name
     * @return array
     */
    public function deleteDatabase($name);
}
