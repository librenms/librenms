<?php

namespace App\Data\Graphing\Legacy;

use App\Models\Device;
use App\Models\Port;

/**
 * What a legacy auth.inc.php file decided
 */
final readonly class LegacyAuthResult
{
    /**
     * @param  array<string, mixed>  $scope  local variables set by the auth file, shared with the graph file
     */
    public function __construct(
        public bool $authorized,
        public ?Device $device,
        public ?Port $port,
        public ?string $subtitle,
        public array $scope,
    ) {
    }
}
