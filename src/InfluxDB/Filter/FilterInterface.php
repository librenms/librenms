<?php
namespace InfluxDB\Filter;

/**
 * Every filter implement this interface
 */
interface FilterInterface
{
    /**
     * Filter metrics
     * @param mixed $anything
     */
    public function filter($anything);
}
