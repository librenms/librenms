<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Models\Syslog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class MaintenanceCleanupSyslog extends LnmsCommand
{
    protected $name = 'maintenance:cleanup-syslog';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $lock = Cache::lock('syslog_purge', 86000);
        if (! $lock->get()) {
            return 1;
        }

        $syslog_purge = LibrenmsConfig::get('syslog_purge');
        if (! is_numeric($syslog_purge)) {
            return 0;
        }

        $deleted_total = 0;
        $deleted_rows = 1;
        while ($deleted_rows > 0) {
            $deleted_rows = Syslog::where('timestamp', '<=', Carbon::now()->subDays($syslog_purge)->toDateTimeString())
                            ->limit(5000)
                            ->delete();
            $deleted_total += $deleted_rows;
        }
        $lock->release();

        $this->line(trans('commands.maintenance:cleanup-syslog.delete', ['days' => $syslog_purge, 'count' => $deleted_total]));

        return 0;
    }
}
