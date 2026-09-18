<?php

namespace App\Listeners;

use App\Action;
use App\Actions\Port\UpdatePortGroupsAction;
use App\Events\DevicePolled;
use Log;

class UpdatePortGroups
{
    public function __construct()
    {
    }

    public function handle(DevicePolled $event): void
    {
        Log::info('### Start Port Groups ###');
        $pg_start = microtime(true);

        $group_changes = Action::execute(UpdatePortGroupsAction::class, device: $event->device);

        $elapsed = round(microtime(true) - $pg_start, 4);

        Log::debug("Port group memberships added: {$group_changes['attached']}  removed: {$group_changes['detached']}");
        Log::info("### End Port Groups ({$elapsed}s) ### \n");
    }
}
