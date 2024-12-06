<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Console\SyntheticDeviceField;
use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
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
        $this->addOption('output', 'o', InputOption::VALUE_REQUIRED, __('commands.report:devices.options.output', ['types' => '[table, csv, none]']), 'table');
        $this->addOption('list-fields');
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
                headerName: "$relationship count");
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

    protected function getSyntheticFields(): array
    {
        return [
            'displayName' => new SyntheticDeviceField('displayName', ['hostname', 'sysName', 'ip', 'display'], fn (Device $device) => $device->displayName(), headerName: 'display name'),
            'location' => new SyntheticDeviceField('location', ['location_id'], fn (Device $device) => $device->location->location, fn (Builder $q) => $q->with('location')),
        ];
    }

    protected function printReport(array $headers, array|Collection $rows): void
    {
        $output = $this->option('output');

        if ($output == 'csv') {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
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
        $this->table($headers, $rows);
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
}
