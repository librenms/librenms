<?php

namespace App\Data\TimeSeries\Rrd;

use App\Data\TimeSeries\Contracts\MetricValidator;
use App\Data\TimeSeries\Contracts\RrdPathResolver;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\Rrd;
use LibreNMS\RRD\RrdPath;

class RrdFileValidator implements MetricValidator
{
    private array $validatedRrdFiles = [];

    public function __construct(
        private readonly RrdPathResolver $resolver
    ) {}

    /**
     * Resolve a metric identity or rrd path and check if the RRD file exists.
     * Caches the result to avoid multiple filesystem checks for the same file.
     *
     * @return string|null The path to the RRD file (relative when using rrdcached) if it exists, null otherwise.
     */
    public function validate(MetricIdentity|RrdPath $metric): ?string
    {
        $path = $metric instanceof RrdPath ? $metric : $this->resolver->resolve($metric);
        $key = $path->relativePath();

        if (! array_key_exists($key, $this->validatedRrdFiles)) {
            $this->validatedRrdFiles[$key] = Rrd::checkRrdExists($path) ? (string) $path : null;
        }

        return $this->validatedRrdFiles[$key];
    }

    public function hasValidFiles(): bool
    {
        foreach ($this->validatedRrdFiles as $path) {
            if ($path !== null) {
                return true;
            }
        }

        return false;
    }

    public function hasAttempted(): bool
    {
        return ! empty($this->validatedRrdFiles);
    }
}
