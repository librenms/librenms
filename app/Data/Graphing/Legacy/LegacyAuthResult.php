<?php

namespace App\Data\Graphing\Legacy;

use App\Data\Graphing\GraphPageContext;

/**
 * What a legacy auth.inc.php file decided
 */
final readonly class LegacyAuthResult
{
    /**
     * @param  GraphPageContext  $pageContext  the device and port the auth file resolved, and the title it set
     * @param  array<string, mixed>  $scope  local variables set by the auth file, shared with the graph file
     */
    public function __construct(
        public bool $authorized,
        public GraphPageContext $pageContext,
        public array $scope,
    ) {
    }
}
