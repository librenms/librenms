<?php

namespace App\Api\Controllers\MetricsApi;

trait MetricsHelpers
{
    /**
     * Escape a value for use in Prometheus labels
     */
    private function escapeLabel(string $v): string
    {
        return str_replace(["\\", '"', "\n"], ["\\\\", '\\"', '\\n'], $v);
    }
}
