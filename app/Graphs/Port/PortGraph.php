<?php

namespace App\Graphs\Port;

use App\Data\Graphing\GraphPageContext;
use App\Graphs\AbstractSeriesGraph;
use App\Models\Port;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Util\Rewrite;

abstract class PortGraph extends AbstractSeriesGraph
{
    public function getPageContext(): GraphPageContext
    {
        $port = $this->requestedPort();

        return new GraphPageContext($port?->device, $port);
    }

    protected function isAuthorized(): bool
    {
        $port = $this->requestedPort();

        return $port !== null && $port->device !== null && $this->canView($port);
    }

    protected function port(): Port
    {
        return $this->requestedPort() ?? throw new NoGraphData;
    }

    /**
     * The in and out fields, swapped when the graph is drawn from the other end of the link
     *
     * @return array{string, string}
     */
    protected function directions(string $in, string $out): array
    {
        return $this->params->inverse ? [$out, $in] : [$in, $out];
    }

    public function getTitle(): string
    {
        $port = $this->port();
        $device = $port->device ?? throw new NoGraphData;
        $title = $device->shortDisplayName() . '::' . strtolower(Rewrite::shortenIfName($port->ifDescr));

        if ($port->ifAlias != '' && $port->ifAlias != $port->ifDescr) {
            $title .= '::' . $port->ifAlias;
        }

        return $title;
    }
}
