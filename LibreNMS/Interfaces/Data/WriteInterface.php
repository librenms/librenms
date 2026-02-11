<?php

namespace LibreNMS\Interfaces\Data;

interface WriteInterface
{
    /**
     * Datastore-independent function which should be used for all polled metrics.
     *
     * metadata known keys:
     *   device      App\Models\Device  non-primary device to write statistics for
     *   rrd_def     RrdDefinition
     *   rrd_name    array|string: the rrd filename, will be processed with rrd_name()
     *   rrd_oldname array|string: old rrd filename to rename, will be processed with rrd_name()
     *   rrd_step             int: rrd step, defaults to 300
     *
     * @param  string  $measurement  Name of this measurement
     * @param  array<string, scalar>  $tags  tags for the data to be able to diffrentiate data sets
     * @param  array<string, scalar>  $fields  The data to update in an associative array
     * @param  array<string, mixed>  $meta  additional data for the datastore (such ass rrd_def)
     */
    public function write(string $measurement, array $fields, array $tags = [], array $meta = []): void;
}
