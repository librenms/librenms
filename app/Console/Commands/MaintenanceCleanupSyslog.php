<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Models\Syslog;
use Carbon\Carbon;
use Symfony\Component\Console\Input\InputArgument;

class MaintenanceCleanupSyslog extends LnmsCommand
{
    protected $name = 'maintenance:cleanup-syslog';

    public function __construct()
    {
        parent::__construct();
        $this->addArgument('days', InputArgument::OPTIONAL);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $syslog_purge = $this->argument('days');

        if ($syslog_purge === null) {
            $syslog_purge = LibrenmsConfig::get('syslog_purge');

            if (! is_numeric($syslog_purge)) {
                $this->warn(__('commands.maintenance:cleanup-syslog.bad_days_setting'));

                return 0;
            }
        } elseif (! is_numeric($syslog_purge)) {
            $this->error(__('commands.maintenance:cleanup-syslog.bad_days_input'));

            return 1;
        }

        if ($syslog_purge <= 0) {
            $this->warn(__('commands.maintenance:cleanup-syslog.disabled'));

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

        $this->line(trans('commands.maintenance:cleanup-syslog.delete', ['days' => $syslog_purge, 'count' => $deleted_total]));

        return 0;
    }
}
