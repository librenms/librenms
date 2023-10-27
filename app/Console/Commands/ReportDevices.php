<?php

namespace App\Console\Commands;

use App\Console\SyntheticDeviceField;
use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ReportDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:devices {-f|--fields=hostname,ip,sysName} {-o|--output=table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Print out data from all devices';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $fields = collect(explode(',', $this->option('fields')))->map(fn($field) => $this->getField($field));
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return 1;
        }
        $output = $this->option('output');

        $headers = $fields->map->headerName()->all();
        $devices = $this->fetchDeviceData($fields);

        $out = fopen('php://output', 'w');
        if ($output == 'csv') {
            fputcsv($out, $headers);
            foreach ($devices as $device) {
                fputcsv($out, $device);
            }
            fclose($out);

            return 0;
        }

        // print table
        $this->table($headers, $devices);

        return 0;
    }

    protected function fetchDeviceData($fields): Collection
    {
        $columns = $fields->pluck('columns')->flatten()->all();
        $query = Device::select($columns);

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
                modifyQuery: fn(Builder $query) => $query->withCount($relationship),
                headerName: "$relationship count");
        }

        // misc synthetic fields
        $custom = match($field) {
            'displayName' => new SyntheticDeviceField($field, ['hostname', 'sysName', 'ip', 'display'], fn(Device $device) => $device->displayName(), headerName: 'display name'),
            default => null,
        };

        if ($custom) {
            return $custom;
        }

        // just a regular column, check that it exists
        if (! Schema::hasColumn('devices', $field)) {
            throw new \Exception("Invalid field: $field");
        }

       return new SyntheticDeviceField($field, [$field]);
    }
}
