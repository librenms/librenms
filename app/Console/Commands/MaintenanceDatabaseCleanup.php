<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Alert;
use App\Models\AlertLog;
use Exception;

class MaintenanceDatabaseCleanup extends LnmsCommand
{
    protected $name = 'maintenance:database-cleanup';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // Delete all orphaned alerts and return the number of rows deleted
            $deleted_alerts = Alert::leftJoin('alert_rules', 'alerts.rule_id', '=', 'alert_rules.id')
                ->whereNull('alert_rules.id')
                ->delete();

            $this->info("Deleted $deleted_alerts orphaned Alerts");

            // Delete all orphaned alert logs and return the number of rows deleted
            $deleted_alert_logs = AlertLog::leftJoin('alert_rules', 'alert_log.rule_id', '=', 'alert_rules.id')
                ->whereNull('alert_rules.id')
                ->delete();

            $this->info("Deleted $deleted_alert_logs orphaned AlertLogs");

            return 0;
        } catch (Exception $e) {
            // Log the error message to the console
            $this->error("Error: {$e->getMessage()}");

            return 1;
        }
    }
}
