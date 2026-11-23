<?php

namespace App\Graphs\Application;

class SystemdAllGraph extends SystemdGraph
{
    protected function stateTypes(array $systemdMapper): array
    {
        return array_keys($systemdMapper);
    }

    protected function prefixLabels(): bool
    {
        return true;
    }
}
