<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
use DeviceCache;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use LibreNMS\Data\Source\SnmpResponse;
use SnmpQuery;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

abstract class SnmpFetch extends LnmsCommand
{
    protected string $type;
    protected array $oids;
    protected ?bool $numeric;
    private string $outputFormat;
    protected int $depth;
    protected string $deviceSpec;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addArgument('device spec', InputArgument::REQUIRED, trans('commands.snmp:fetch.arguments.device spec'));
        $this->addArgument('oid(s)', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, trans('commands.snmp:fetch.arguments.oid(s)'));
        $this->addOption('output', 'o', InputOption::VALUE_REQUIRED, trans('commands.snmp:fetch.options.output', ['formats' => '[value, values, table, index-table]']));
        $this->addOption('depth', 'd', InputOption::VALUE_REQUIRED, trans('commands.snmp:fetch.options.depth'), 1);
        $this->addOption('numeric', 'i', InputOption::VALUE_NONE, trans('commands.snmp:fetch.options.numeric'));
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->configureOutputOptions();

        $this->validate([
            'output' => ['nullable', Rule::in(['value', 'values', 'table', 'index-table'])],
        ]);

        $this->type = substr($this->name, 5); // 'snmp:<type>'
        $this->deviceSpec = $this->argument('device spec');
        $this->oids = $this->argument('oid(s)') ?: [];
        $this->numeric = $this->hasOption('numeric') ? $this->option('numeric') : null;  // // @phpstan-ignore-line larastan.console.undefinedOption
        $this->depth = (int) $this->option('depth');
        $this->outputFormat = $this->option('output') ?: match ($this->type) {
            'walk' => 'table',
            'translate' => 'values',
            default => 'value',
        };

        $devices = $this->getDevices();

        if ($devices->isEmpty()) {
            $this->error(trans('commands.snmp:fetch.not_found'));

            return 1;
        }

        $return = 0;

        foreach ($devices as $device) {
            $device_name = $device->displayName();
            if ($device_name) {
                $this->info($device_name . ':');
            }

            $res = $this->fetchData($device);

            if (! $res->isValid()) {
                $this->warn(trans('commands.snmp:fetch.failed'));
                $this->line($res->getErrorMessage());
                $res->isValid();

                $return = 1;
                continue;
            }

            switch ($this->outputFormat) {
                case 'value':
                    $this->line($res->value());

                    continue 2;
                case 'values':
                    $values = array_map(fn ($value, $oid) => ["<fg=bright-blue>$oid</>", $value], $res->values(), array_keys($res->values()));

                    if (count($values) === 1) {
                        [$oid, $value] = array_pop($values);
                        $this->line("$oid = $value");

                        continue 2;
                    }

                    $headers = $this->type == 'translate'
                        ? [__('commands.snmp:fetch.textual'), __('commands.snmp:fetch.numeric')]
                        : [__('commands.snmp:fetch.oid'), __('commands.snmp:fetch.value')];
                    $this->table($headers, $values);

                    continue 2;
                case 'table':
                    $this->printYamlLike($res->table($this->depth));

                    continue 2;
                case 'index-table':
                    $this->printYamlCombinedKey($res->valuesByIndex());

                    continue 2;
            }
        }

        return $return;
    }

    protected function printYamlLike(array $data, int $indent = 0): void
    {
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                // recurse
                $this->printDataLine($key, '', $indent, 'blue');
                $this->printYamlLike($item, $indent + 2);
                continue;
            }

            // data items
            $this->printDataLine($key, $item, $indent, 'bright-blue');
        }
    }

    protected function printYamlCombinedKey(array $data, string $carry = ''): void
    {
        // we found the full key, print it
        if (! is_array(Arr::first($data))) {
            $this->printDataLine($carry, '', 0, 'blue');
        }

        foreach ($data as $key => $item) {
            if (is_array($item)) {
                // recurse
                $this->printYamlCombinedKey($item, $carry ? "$carry.$key" : $key);
                continue;
            }

            // data items
            $this->printDataLine($key, $item, 2, 'bright-blue');
        }
    }

    protected function getDevices(): \Illuminate\Support\Collection
    {
        return Device::whereDeviceSpec($this->deviceSpec)->pluck('device_id')
            ->map(fn ($device_id) => DeviceCache::get($device_id));
    }

    protected function fetchData(Device $device): SnmpResponse
    {
        $type = $this->type;

        return SnmpQuery::make()
            ->enumStrings()
            ->numeric($this->numeric)
            ->device($device)
            ->$type($this->oids);
    }

    protected function printDataLine(string $key, string $data, int $indent, string $color): void
    {
        $this->line(str_repeat(' ', $indent) . "<fg=$color>$key</>: $data");
    }
}
