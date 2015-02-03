<?php
namespace InfluxDB\Adapter;

/**
 * Every InfluxDB adapter implements this interface
 */
interface AdapterInterface
{
    /**
     * Send series into database
     * @param mixed $message
     * @param string|boolean $timePrecision
     */
    public function send($message, $timePrecision = false);
}
