<?php

namespace App\Graphs\Application;

class SystemdLoadGraph extends SystemdGraph
{
    protected function stateTypes(array $systemdMapper): array
    {
        return ['load'];
    }

    protected function prefixLabels(): bool
    {
        return false;
    }
}
