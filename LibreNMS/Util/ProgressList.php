<?php

namespace LibreNMS\Util;

use Illuminate\Support\Facades\Log;

/**
 * Output a single line in the form "Label: item item ...."
 * Start and end the list in the same scope.
 */
class ProgressList
{
    private function __construct()
    {
    }

    public static function start(string $label): self
    {
        Log::info("$label: ", ['nlb' => true]);

        return new self;
    }

    public function add(string $item): void
    {
        Log::info("$item ", ['nlb' => true]);
    }

    /**
     * Progress marker such as . + U -
     */
    public function mark(string $marker): void
    {
        Log::info($marker, ['nlb' => true]);
    }

    public function end(): void
    {
        Log::info(PHP_EOL, ['nlb' => true]);
    }
}
