<?php

namespace App\Console\Commands;

use App\Console\DynamicInputOption;
use App\Console\LnmsCommand;
use App\Console\SyntheticDeviceField;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use LibreNMS\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ReportDevices extends LnmsCommand
{
    protected $name = 'report:devices';
    const NONE_SEPERATOR = "\t";

    public function __construct()
    {
        parent::__construct();
        $this->addArgument('device spec', InputArgument::OPTIONAL);
        $this->addOption('fields', 'f', InputOption::VALUE_REQUIRED, default: 'hostname,ip');
        $this->addOption('output', 'o', InputOption::VALUE_REQUIRED, __('commands.report:devices.options.output', ['types' => '[table, csv, json, none]']), 'table');
        $this->addOption('list-fields');
        $this->addOption('list-relationships', '-L', InputOption::VALUE_NONE);
        $this->addOption('no-header', 't', InputOption::VALUE_NONE);
        $this->addOption('devices-as-array', 'a', InputOption::VALUE_NONE);
        $this->addOption('relationships', 'r', InputOption::VALUE_OPTIONAL);
        $this->addOption('all-relationships', 'R', InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('list-fields')) {
            $this->printFields();

            return 0;
        }

        if ($this->option('list-relationships')) {
            $this->printRelationships();

            return 0;
        }

        // put this here since this does not need the complexity of the rest and functions in a very different manner
        if ($this->option('output') == 'json') {
            return $this->jsonRequestHandler();
        }

        try {
            $fields = collect(explode(',', $this->option('fields')))->map(fn ($field) => $this->getField($field));
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $headers = $fields->map->headerName()->all();
        $devices = $this->fetchDeviceData($fields);

        $this->printReport($headers, $devices);

        return 0;
    }

    protected function fetchDeviceData($fields): Collection
    {
        $columns = $fields->pluck('columns')->flatten()->all();
        $query = Device::whereDeviceSpec($this->argument('device spec'))->select($columns);

        // apply any field query modifications
        foreach ($fields as $field) {
            $field->modifyQuery($query);
        }

        // fetch data and call the toString method for each field.
        return $query->get()->map(function (Device $device) use ($fields) {
            $data = [];
            foreach ($fields as $field) {
                $data[$field->name] = $field->toString($device);
            }

            return $data;
        });
    }

    protected function getField(string $field): SyntheticDeviceField
    {
        // relationship counts
        if (str_ends_with($field, '_count')) {
            [$relationship] = explode('_', $field, -1);
            if (! (new Device)->isRelation($relationship)) {
                throw new \Exception("Invalid field: $field");
            }

            return new SyntheticDeviceField(
                $field,
                modifyQuery: fn (Builder $query) => $query->withCount($relationship),
                headerName: "$relationship count"
            );
        }

        // misc synthetic fields
        $syntheticFields = $this->getSyntheticFields();
        if (isset($syntheticFields[$field])) {
            return $syntheticFields[$field];
        }

        // just a regular column, check that it exists
        if (! Schema::hasColumn('devices', $field)) {
            throw new \Exception("Invalid field: $field");
        }

        return new SyntheticDeviceField($field, [$field]);
    }

    protected function getRelationships(): array
    {
        $relationships = Device::definedRelations();
        $port_relationships = Port::definedRelations();
        foreach ($port_relationships as $relationship) {
            $relationships[] = 'ports.' . $relationship;
        }

        return $relationships;
    }

    protected function getSyntheticFields(): array
    {
        return [
            'displayName' => new SyntheticDeviceField('displayName', ['hostname', 'sysName', 'ip', 'display'], fn (Device $device) => $device->displayName(), headerName: 'display name'),
            'location' => new SyntheticDeviceField('location', ['location_id'], fn (Device $device) => $device->location->location, fn (Builder $q) => $q->with('location')),
            'os_text' => new SyntheticDeviceField('os_text', ['os'], fn (Device $device) => Config::getOsSetting($device->os, 'text'), headerName: 'os text'),
        ];
    }

    protected function jsonRequestHandler(): int
    {
        $has_relationships = false;
        $relationships = []; // make phpstan happy
        if ($this->option('relationships')) {
            /*
             * Clean up the return to ensure we have no white space or unintended empty items.
             * /\s/ to remove any white space
             * /\,\,+/ to remove consecutive ','
             * /\,+$/ to remove any trailing ','
             * /^\,+/ to remove any leading ','
             */
            $relationships = explode(',', preg_replace('/\,+/', ',', preg_replace('/(\s+|^\,+|\,+$)/', '', $this->option('relationships'))));
            $has_relationships = true;
        } elseif ($this->option('all-relationships')) {
            $relationships = $this->getRelationships();
            $has_relationships = true;
        }

        $devices = Device::when($has_relationships, fn ($q) => $q->with($relationships))
            ->whereDeviceSpec($this->argument('device spec'))->get();

        if (! $this->option('devices-as-array')) {
            foreach ($devices as $device) {
                $this->line(json_encode($device));
            }

            return 0;
        }

        $this->line(json_encode($devices));

        return 0;
    }

    protected function printReport(array $headers, array|Collection $rows): void
    {
        $output = $this->option('output');

        if ($output == 'csv') {
            $out = fopen('php://output', 'w');
            if (! $this->option('no-header')) {
                fputcsv($out, $headers);
            }
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);

            return;
        }

        if ($output == 'none') {
            foreach ($rows as $row) {
                $this->line(implode(self::NONE_SEPERATOR, $row));
            }

            return;
        }

        // print table
        if (! $this->option('no-header')) {
            $this->table($headers, $rows);
        } else {
            $this->table([], $rows);
        }
    }

    protected function printFields(): void
    {
        $this->info(__('commands.report:devices.columns'));
        $columns = Schema::getColumnListing('devices');
        foreach ($columns as $column) {
            $this->line($column);
        }

        $this->info(__('commands.report:devices.synthetic'));
        $synthetic = array_keys($this->getSyntheticFields());
        foreach ($synthetic as $field) {
            $this->line($field);
        }

        $this->info(__('commands.report:devices.counts'));
        $relationships = Device::definedRelations();
        foreach ($relationships as $relationship) {
            $this->line($relationship . '_count');
        }
    }

    protected function printRelationships(): void
    {
        $relationships = $this->getRelationships();
        foreach ($relationships as $relationship) {
            $this->line($relationship);
        }
    }

    public function completeOptionValue(DynamicInputOption $option, string $current): ?Collection
    {
        if ($option->getName() == 'fields') {
            return collect()
                ->merge(Schema::getColumnListing('devices'))
                ->merge(array_keys($this->getSyntheticFields()))
                ->merge(Device::definedRelations())
                ->when($current, fn ($c) => $c->filter(fn ($i) => str_starts_with($i, $current)));
        }

        if ($option->getName() == 'relationships') {
            return collect()
                ->merge($this->getRelationships())
                ->when($current, fn ($c) => $c->filter(fn ($i) => str_starts_with($i, $current)));
        }

        return null;
    }
}
