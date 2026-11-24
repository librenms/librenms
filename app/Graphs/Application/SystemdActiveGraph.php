<?php

namespace App\Graphs\Application;

class SystemdActiveGraph extends SystemdGraph
{
    protected function stateTypes(array $systemdMapper): array
    {
        return ['active'];
    }

    protected function prefixLabels(): bool
    {
        return false;
    }
}
