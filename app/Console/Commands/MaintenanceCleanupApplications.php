<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Models\Application;
use Symfony\Component\Console\Input\InputArgument;

class MaintenanceCleanupApplications extends LnmsCommand
{
    protected $name = 'maintenance:cleanup-applications';

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
        $days = $this->argument('days');
        $fromConfig = $days === null;

        if ($fromConfig) {
            $days = LibrenmsConfig::get('applications_purge');
        }

        if (filter_var($days, FILTER_VALIDATE_INT) === false) {
            if ($fromConfig) {
                $this->warn(__('commands.maintenance:cleanup-applications.bad_days_setting'));

                return 0;
            }

            $this->error(__('commands.maintenance:cleanup-applications.bad_days_input'));

            return 1;
        }

        if ($days <= 0) {
            $this->warn(__('commands.maintenance:cleanup-applications.disabled'));

            return 0;
        }

        $purged = Application::purgeTrashed((int) $days);

        if ($purged['applications'] > 0) {
            $this->line(trans('commands.maintenance:cleanup-applications.purged', [
                'applications' => $purged['applications'],
                'metrics' => $purged['metrics'],
                'days' => $days,
            ]));
        }

        return 0;
    }
}
