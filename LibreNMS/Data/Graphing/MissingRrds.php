<?php

namespace LibreNMS\Data\Graphing;

use LibreNMS\RRD\RrdPath;

/**
 * Rrd files that were missing while drawing a graph.
 * Graphs with optional series skip these files when the graph is rebuilt.
 */
class MissingRrds
{
    /** @var string[] full paths reported by rrdtool */
    private array $files = [];

    /**
     * Record a missing file
     *
     * @return bool false if the file was already known to be missing
     */
    public function add(string $file): bool
    {
        if ($this->has($file)) {
            return false;
        }

        $this->files[] = $file;

        return true;
    }

    /**
     * Check if a file is known to be missing.
     * rrdtool reports full paths, while graphs may use paths relative to the rrdcached base directory.
     */
    public function has(RrdPath|string $rrd): bool
    {
        $path = (string) $rrd;

        foreach ($this->files as $file) {
            if ($file === $path || str_ends_with($file, '/' . ltrim($path, '/'))) {
                return true;
            }
        }

        return false;
    }

    public function isEmpty(): bool
    {
        return empty($this->files);
    }

    public function first(): ?string
    {
        return $this->files[0] ?? null;
    }
}
