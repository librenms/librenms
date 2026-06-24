<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use Illuminate\Support\Facades\Cache;
use LibreNMS\Alert\AlertNotifications;
use LibreNMS\Util\Debug;
use Symfony\Component\Console\Input\InputOption;

class AlertsNotify extends LnmsCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'alerts:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for any pending alerts and deliver them via defined transports';

    public function __construct()
    {
        parent::__construct();
        $this->addOption('scheduler', 'S', InputOption::VALUE_REQUIRED, 'The scheduler invoking this command');
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $invokedScheduler = $this->option('scheduler');
        $configuredScheduler = LibrenmsConfig::get('schedule_type.ping');
        if ($invokedScheduler && $configuredScheduler !== 'legacy' && $invokedScheduler !== $configuredScheduler) {
            if (Debug::isEnabled() || $this->getOutput()->isVerbose()) {
                $this->info("Alerts are not enabled for $invokedScheduler scheduling. (Configured: $configuredScheduler)");
            }

            return 0;
        }

        $alerts_lock = Cache::lock('alerts', LibrenmsConfig::get('service_alerting_frequency'));
        if ($alerts_lock->get()) {
            $alerts = new AlertNotifications();
            if (!LibrenmsConfig::get('alert.disable')) {
                $this->line('Start: ' . date('r'));
                $this->line('ClearStaleAlerts():');
                $alerts->clearStaleAlerts();
                $this->line("RunFollowUp():");
                $alerts->runFollowUp();
                $this->line("AlertNotifications():");
                $alerts->runAlerts();
                $this->line("RunAcks():");
                $alerts->runAcks();
                $this->line('End  : ' . date('r'));
            }
            $alerts_lock->release();
        }

        return 0;
    }
}
