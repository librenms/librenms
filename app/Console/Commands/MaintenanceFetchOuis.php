<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use LibreNMS\Config;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MaintenanceFetchOuis extends LnmsCommand
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'maintenance:fetch-ouis';

    protected string $mac_oui_url = 'https://www.wireshark.org/download/automated/data/manuf';
    protected int $min_refresh_days = 6;
    protected int $max_wait_seconds = 900;
    protected int $upsert_chunk_size = 1000;

    public function __construct()
    {
        parent::__construct();

        $this->addOption('force', null, InputOption::VALUE_NONE);
        $this->addOption('wait', null, InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');

        if (Config::get('mac_oui.enabled') !== true && ! $force) {
            $this->line(trans('commands.maintenance:fetch-ouis.disabled', ['setting' => 'mac_oui.enabled']));

            if (! $this->confirm(trans('commands.maintenance:fetch-ouis.enable_question'))) {
                return 0;
            }

            Config::persist('mac_oui.enabled', true);
        }

        // We want to refresh after at least 6 days
        $lock = Cache::lock('vendor_oui_db_refresh', 86400 * $this->min_refresh_days);
        if (! $lock->get() && ! $force) {
            $this->warn(trans('commands.maintenance:fetch-ouis.recently_fetched'));

            return 0;
        }

        // wait for 0-15 minutes to prevent stampeding herd
        if ($this->option('wait')) {
            $seconds = rand(1, $this->max_wait_seconds);
            $minutes = (int) round($seconds / 60);
            $this->info(trans_choice('commands.maintenance:fetch-ouis.waiting', $minutes, ['minutes' => $minutes]));
            sleep($seconds);
        }

        $this->line(trans('commands.maintenance:fetch-ouis.starting'));

        try {
            $this->line('  -> ' . trans('commands.maintenance:fetch-ouis.downloading') . ' ...');
            $csv_data = \LibreNMS\Util\Http::client()->get($this->mac_oui_url)->body();

            // convert the csv into an array to be consumed by upsert
            $this->line('  -> ' . trans('commands.maintenance:fetch-ouis.processing') . ' ...');
            $ouis = $this->buildOuiList($csv_data);

            $this->line('  -> ' . trans('commands.maintenance:fetch-ouis.saving') . ' ...');
            $count = 0;
            foreach (array_chunk($ouis, $this->upsert_chunk_size) as $oui_chunk) {
                $count += DB::table('vendor_ouis')->upsert($oui_chunk, 'oui');
            }

            $this->info(trans_choice('commands.maintenance:fetch-ouis.success', $count, ['count' => $count]));

            return 0;
        } catch (\Exception|\ErrorException $e) {
            $this->error(trans('commands.maintenance:fetch-ouis.error'));
            $this->error('Exception: ' . get_class($e));
            $this->error($e);

            $lock->release(); // We did not succeed, so we'll try again next time

            return 1;
        }
    }

    private function buildOuiList(string $csv_data): array
    {
        $ouis = [];

        foreach (explode("\n", rtrim($csv_data)) as $csv_line) {
            // skip comments
            if (str_starts_with($csv_line, '#')) {
                continue;
            }

            [$oui, , $vendor] = str_getcsv($csv_line, "\t"); // index 1 = short vendor

            $oui = strtolower(str_replace(':', '', $oui)); // normalize oui
            $prefix_index = strpos($oui, '/');

            // check for non-/24 oui
            if ($prefix_index !== false) {
                // find prefix length
                $prefix_length = (int) substr($oui, $prefix_index + 1);

                // 4 bits per character: /28 = 7 /36 = 9
                $substring_length = (int) floor($prefix_length / 4);

                $oui = substr($oui, 0, $substring_length);
            }
            $vendor = trim($vendor);
            $oui = trim($oui);

            // Add to the list of vendor ids
            $ouis[] = [
                'vendor' => $vendor,
                'oui' => $oui,
            ];

            if ($this->verbosity == OutputInterface::VERBOSITY_DEBUG) {
                $this->line(trans('commands.maintenance:fetch-ouis.vendor_update', ['vendor' => $vendor, 'oui' => $oui]));
            }
        }

        return $ouis;
    }
}
