<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk <s.hoogendijk@tech.leaseweb.com>
 */

namespace Leaseweb\InfluxDB\Query;

use Leaseweb\InfluxDB\Database;

/**
 * Class QueryBuilder
 *
 * Abstraction class for getting time series out of InfluxDB
 *
 * Sample usage:
 *
 * $series = new QueryBuilder($db);
 * $series->percentile(95)->setTimeRange($timeFrom, $timeTo)->getResult();
 *
 * $series->select('*')->from('*')->getResult();
 *
 * @package Leaseweb\InfluxDB
 */
class Builder
{

    protected $db = null;
    protected $selection = '*';
    protected $where = array();
    protected $startTime = null;
    protected $endTime = null;
    protected $metric = null;

    /**
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $metric The metric to select (required)
     *
     * @return $this
     */
    public function from($metric)
    {
        $this->metric = $metric;

        return $this;
    }

    /**
     * Custom select method
     *
     * example:
     *
     * $series->select('sum(value)',
     *
     * @param string $customSelect
     *
     * @return $this
     */
    public function select($customSelect)
    {
        $this->selection = $customSelect;

        return $this;
    }

    /**
     * @param array $conditions
     *
     * Example: array('time > now()', 'time < now() -1d');
     *
     * @return $this
     */
    public function where(array $conditions)
    {

        foreach ($conditions as $condition) {
            $this->where[] = $condition;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function selectAll()
    {
        $this->selection = '*';

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function count($field = 'type')
    {
        $this->selection = sprintf('count(%s)', $field);

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function mean($field = 'type')
    {
        $this->selection = sprintf('mean(%s)', $field);

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function sum($field = 'type')
    {
        $this->selection = sprintf('sum(%s)', $field);

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function first($field = 'type')
    {
        $this->selection = sprintf('first(%s)', $field);

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function last($field = 'type')
    {
        $this->selection = sprintf('first(%s)', $field);

        return $this;
    }

    /**
     * Set's the time range to select data from
     *
     * @param int $from Unix timestamp from
     * @param int $to   Unix timestamp to
     *
     * @return $this
     */
    public function setTimeRange($from, $to)
    {
        $fromDate = date('Y-m-d H:i:s', $from);
        $toDate = date('Y-m-d H:i:s', $to);

        $this->where(array("time > '$fromDate'", "time < '$toDate'"));

        return $this;
    }

    /**
     * @param int $percentile Percentage to select (for example 95 for 95th percentile billing)
     *
     * @return $this
     */
    public function percentile($percentile = 95)
    {
        $this->selection = sprintf('percentile(value, %d)', (int) $percentile);

        return $this;
    }

    /**
     * Gets the result from the database (builds the query)
     *
     * @param bool $raw always return the ResultSeriesObjects, even when using an aggregation function
     *
     * @return array|null
     */
    public function getResult($raw = false)
    {
        $query = sprintf("SELECT %s FROM %s", $this->selection, $this->metric);
        $aggregateKey = null;

        if (!$this->metric) {
            throw new \InvalidArgumentException('No metric provided to from()');
        }

        if (preg_match("/([a-z]+)\(/i", $this->selection, $matches)) {
            $aggregateKey = $matches[1];
        }

        for ($i=0; $i < count($this->where); $i++) {
            $selection = 'WHERE';
            if ($i > 0) {
                $selection = 'AND';
            }

            $clause = $this->where[$i];
            $query .= ' ' . $selection . ' ' . $clause;

        }

        $queryResult = $this->db->query($query);

        if ($queryResult && $aggregateKey && !$raw) {
            return (float) $queryResult[0]->$aggregateKey;
        }

        return $queryResult;
    }
}