<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

/**
 * A graph of one docker container stat, a line for the container in the container var or for each container
 * of the application. Each container is a file of its own: app-docker-<id>-<container>.rrd.
 */
abstract class DockerContainerGraph extends ApplicationGraph
{
    /**
     * The field of the stat in the container file, such as cpu_usage
     */
    abstract protected function field(): string;

    abstract protected function legendTitle(): string;

    protected function validation(): array
    {
        return [...parent::validation(), 'container' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $container = $this->params->input->string('container');
        $containers = $container === null ? $this->application()->data['containers'] ?? [] : [$container];

        $series = [];
        foreach ($containers as $containerName) {
            $series[$containerName] = new DataSeries($this->metric(['container' => $containerName]), $this->field(), (string) $containerName);
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: $this->legendTitle(), palette: 'mega');
    }
}
