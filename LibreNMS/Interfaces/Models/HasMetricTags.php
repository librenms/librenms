<?php

namespace LibreNMS\Interfaces\Models;

interface HasMetricTags
{
    /**
     * Get array of tags with key fields and values, they identify the model's metrics
     *
     * @return array<string, string|int>
     */
    public function tags(): array;
}
