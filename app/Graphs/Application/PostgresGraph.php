<?php

namespace App\Graphs\Application;

use App\Data\TimeSeries\MetricIdentity;
use LibreNMS\Exceptions\NoGraphData;

/**
 * A graph of Postgres stats. The totals are stored in app-postgres-<app_id>.rrd and each database has its own
 * app-postgres-<app_id>-<database>.rrd, chosen with the optional database request var.
 */
abstract class PostgresGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'database' => 'nullable|string'];
    }

    /**
     * The metric of the requested database, or of the totals when none is requested
     *
     * @throws NoGraphData
     */
    protected function databaseMetric(): MetricIdentity
    {
        $database = $this->params->input->string('database');

        return $database === null ? $this->metric() : $this->metric(['database' => $database]);
    }
}
