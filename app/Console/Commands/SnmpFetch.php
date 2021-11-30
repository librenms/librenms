<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
use DeviceCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use SnmpQuery;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SnmpFetch extends LnmsCommand
{
    protected $name = 'snmp:fetch';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addArgument('device spec', InputArgument::REQUIRED);
        $this->addArgument('oid', InputArgument::REQUIRED);
        $this->addOption('type', 't', InputOption::VALUE_REQUIRED, trans('commands.snmp:fetch.options.type', ['types' => '[get, walk, next, translate]']), 'get');
        $this->addOption('output', 'o', InputOption::VALUE_REQUIRED, trans('commands.snmp:fetch.options.output', ['formats' => '[value, values, table]']));
        $this->addOption('depth', 'd', InputOption::VALUE_REQUIRED, null, 1);
        $this->addOption('numeric', 'i', InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->validate([
            'type' => Rule::in(['walk', 'get', 'next', 'translate']),
            'output' => ['nullable', Rule::in(['value', 'values', 'table'])],
        ]);

        $spec = $this->argument('device spec');
        $device_ids = Device::query()->when($spec !== 'all', function (Builder $query) use ($spec) {
            return $query->where('device_id', $spec)
                ->orWhere('hostname', 'regexp', "^$spec$");
        })->pluck('device_id');

        if ($device_ids->isEmpty()) {
            $this->error(trans('commands.snmp:fetch.not_found'));

            return 1;
        }

        $return = 0;

        foreach ($device_ids as $device_id) {
            DeviceCache::setPrimary($device_id);
            $this->info(DeviceCache::getPrimary()->displayName() . ':');

            $type = $this->option('type');
            $output = $this->option('output')
                ?: ($type == 'walk' ? 'table' : 'value');

            $query = SnmpQuery::make();
            if ($this->option('numeric')) {
                $query->numeric();
            }

            /** @var \LibreNMS\Data\Source\SnmpResponse $res */
            $res = $query->$type($this->argument('oid'));

            if (! $res->isValid()) {
                $this->warn(trans('commands.snmp:fetch.failed'));
                $this->line($res->getErrorMessage());
                $res->isValid();

                $return = 1;
                continue;
            }

            switch ($output) {
                case 'value':
                    $this->line($res->value());

                    continue 2;
                case 'values':
                    $values = [];
                    foreach ($res->values() as $oid => $value) {
                        $values[] = [$oid, $value];
                    }
                    $this->table(
                        [trans('commands.snmp:fetch.oid'), trans('commands.snmp:fetch.value')],
                        $values
                    );

                    continue 2;
                case 'table':
                    dump($res->table((int) $this->option('depth')));

                    continue 2;
            }
        }

        return $return;
    }
}
