<?php

namespace App\Data\TimeSeries\Rrd;

use App\Data\TimeSeries\Contracts\MetricValidator;
use App\Data\TimeSeries\Contracts\RrdPathResolver;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;

class RrdFileValidator implements MetricValidator
{
    private array $validatedRrdFiles = [];

    public function __construct(
        private readonly RrdPathResolver $resolver
    ) {}

    /**
     * Resolve a metric identity or filename and check if the RRD file exists.
     * Caches the result to avoid multiple filesystem checks for the same file.
     *
     * @param  MetricIdentity|null  $metric
     * @param  array{filename?: string}  $extra
     * @return string|null The absolute path to the RRD file if it exists, null otherwise.
     */
    public function validate(?MetricIdentity $metric = null, array $extra = []): ?string
    {
        $filename = $extra['filename'] ?? null;

        if ($filename === null && $metric instanceof MetricIdentity) {
            $filename = $this->resolver->resolve($metric);
        }

        if ($filename === null) {
            return null;
        }

        if (! str_starts_with($filename, '/')) {
            $filename = rtrim((string) LibrenmsConfig::get('rrd_dir'), '/') . '/' . $filename;
        }

        if (array_key_exists($filename, $this->validatedRrdFiles)) {
            return $this->validatedRrdFiles[$filename];
        }

        $exists = Rrd::checkRrdExists($filename);
        $result = $exists ? $filename : null;
        $this->validatedRrdFiles[$filename] = $result;

        return $result;
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
