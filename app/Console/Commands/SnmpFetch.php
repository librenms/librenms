<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
use Illuminate\Validation\Rule;
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
        $device_id = Device::where('device_id', $spec)->orWhere('hostname', $spec)->value('device_id');
        if ($device_id == null) {
            $this->error(trans('commands.snmp:fetch.not_found'));

            return 1;
        }

        \DeviceCache::setPrimary($device_id);

        $type = $this->option('type');
        $output = $this->option('output')
            ?: ($type == 'walk' ? 'table' : 'value');

        /** @var \LibreNMS\Data\Source\SnmpResponse $res */
        $res = \NetSnmp::numeric($this->option('numeric'))
            ->$type($this->argument('oid'));

        if (! $res->isValid()) {
            $this->alert(trans('commands.snmp:fetch.failed'));
            $this->line($res->getErrorMessage());
            $res->isValid();

            return 1;
        }

        switch ($output) {
            case 'value':
                $this->line($res->value());

                return 0;
            case 'values':
                $values = [];
                foreach ($res->values() as $oid => $value) {
                    $values[] = [$oid, $value];
                }
                $this->table(
                    [trans('commands.snmp:fetch.oid'), trans('commands.snmp:fetch.value')],
                    $values
                );

                return 0;
            case 'table':
                dump($res->table((int) $this->option('depth')));

                return 0;
        }

        return 0;
    }
}
