<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk <s.hoogendijk@tech.leaseweb.com>
 */

namespace InfluxDB\Query;

use InfluxDB\Database;
use InfluxDB\ResultSet;

/**
 * Class Builder
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
 * @todo add inner join
 * @todo add merge
 *
 * @package InfluxDB\Query
 */
class Builder
{

    protected $db = null;
    protected $selection = '*';
    protected $where = array();
    protected $startTime = null;
    protected $endTime = null;
    protected $metric = null;
    protected $limitClause = '';

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
    public function median($field = 'type')
    {
        $this->selection = sprintf('median(%s)', $field);

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
        $this->selection = sprintf('last(%s)', $field);

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
     * Limit the ResultSet to n records
     *
     * @param int $count
     *
     * @return $this
     */
    public function limit($count)
    {
        $this->limitClause = sprintf(' LIMIT %s', (int) $count);

        return $this;
    }


    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->parseQuery();
    }

    /**
     * Gets the result from the database (builds the query)
     *
     * @return ResultSet
     */
    public function getResultSet()
    {
        return  $this->db->query($this->parseQuery());
    }

    /**
     * @return string
     */
    protected function parseQuery()
    {
        $query = sprintf("SELECT %s FROM %s", $this->selection, $this->metric);

        if (!$this->metric) {
            throw new \InvalidArgumentException('No metric provided to from()');
        }

        for ($i=0; $i < count($this->where); $i++) {
            $selection = 'WHERE';
            if ($i > 0) {
                $selection = 'AND';
            }

            $clause = $this->where[$i];
            $query .= ' ' . $selection . ' ' . $clause;

        }

        if ($this->limitClause) {
            $query .= $this->limitClause;
        }

        return $query;
    }
}