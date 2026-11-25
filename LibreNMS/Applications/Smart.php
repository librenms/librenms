<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Smart extends ApplicationDefinition
{
    public const string TYPE = 'smart';
    protected const ?string DOCS = 'SMART';
    protected const ?string NAME = 'SMART';

    protected const ?array OVERVIEW = [
        'id5',
        'id9',
        'id10',
        'id173',
        'id183',
        'id184',
        'id187',
        'id188',
        'maxtemp',
        'id190',
        'id194',
        'id196',
        'id197',
        'id198',
        'id199',
        'id231',
        'id233',
    ];

    protected const array ATTRIBUTES = [
        'id5' => 'ID# 5, Reallocated Sectors Count',
        'id9' => 'ID# 9, Power On Hours',
        'id10' => 'ID# 10, Spin Retry Count',
        'id173' => 'ID# 173, SSD Wear Leveller Worst Case Erase Count',
        'id177' => 'ID# 177, SSD Wear Leveling Count',
        'id183' => 'ID# 183, Detected Uncorrectable Bad Blocks',
        'id184' => 'ID# 184, End-to-End error / IOEDC',
        'id187' => 'ID# 187, Reported Uncorrectable Errors',
        'id188' => 'ID# 188, Command Timeout',
        'id190' => 'ID# 190, Airflow Temperature (C)',
        'id194' => 'ID# 194, Temperature (C)',
        'id196' => 'ID# 196, Reallocation Event Count',
        'id197' => 'ID# 197, Current Pending Sector Count',
        'id198' => 'ID# 198, Uncorrectable Sector Count / Offline Uncorrectable / Off-Line Scan Uncorrectable Sector Count',
        'id199' => 'ID# 199, UltraDMA CRC Error Count',
        'id231' => 'ID# 231, SSD Life Left',
        'id232' => 'ID# 232, Available Reserved Space',
        'id233' => 'ID# 233, Media Wearout Indicator',
    ];

    protected const array DISK_FIELDS = [
        'disk' => 'Disk',
        'serial' => 'Serial',
        'vendor' => 'Vendor',
        'product' => 'Product',
        'model_family' => 'Model Family',
        'model_number' => 'Model Number',
        'device_model' => 'Device Model',
        'revision' => 'Revision',
        'fw_version' => 'FW Version',
        'form_factor' => 'Form Factor',
        'rpm' => 'RPM',
    ];

    protected const array DISK_GRAPHS = [
        'big5' => 'Reliability / Age',
        'temp' => 'Temperature',
        'ssd' => 'SSD-specific',
        'other' => 'Other',
        'tests_status' => 'S.M.A.R.T self-tests results',
        'tests_ran' => 'S.M.A.R.T self-tests run count',
        'runtime' => 'Power On Hours',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $data = $application->data ?? [];
        $disks = $data['disks'] ?? [];
        ksort($disks);
        $disk = $page->option('disk');
        $choices = ['' => 'All Drives'];
        foreach ($disks as $name => $info) {
            $choices[$name] = $this->driveLabel($name, $info);
        }
        $page->addFilter('Drives', 'disk', $choices, $disk);

        if ($disk === null) {
            $hasData = $data['has'] ?? [];
            $graphs = array_intersect_key(self::ATTRIBUTES, array_filter($hasData, fn ($value) => $value === 1));
            if (isset($graphs['id190']) || isset($graphs['id194'])) {
                $graphs = ['maxtemp' => 'Max Temp(C), Airflow Temperature or Device'] + $graphs;
            }
        } else {
            $currentDisk = $disks[$disk] ?? [];
            if (! isset($data['legacy'])) {
                foreach (self::DISK_FIELDS as $field => $label) {
                    if (isset($currentDisk[$field])) {
                        $page->details[$label] = $currentDisk[$field];
                    }
                }
                if (isset($currentDisk['selftest_log'])) {
                    $page->text['Self-test log'] = str_replace('n#', "\n#", $currentDisk['selftest_log']);
                }
            }

            $graphs = self::DISK_GRAPHS;
            if (($currentDisk['is_ssd'] ?? 0) !== 1) {
                unset($graphs['ssd']);
            }
        }

        $page->addGraphs($graphs, ['disk' => $disk, 'scale_min' => '0']);
    }

    /** @param  array<string, mixed>  $info */
    private function driveLabel(string $name, array $info): string
    {
        $health = match ($info['health_pass'] ?? null) {
            1 => ' (OK)',
            0 => ' (FAIL)',
            default => '',
        };
        $warnings = [
            'over_temp' => 'Overheating',
            'dev_error' => 'Polling Error',
            'read_failure' => 'Read Failure',
            'unknown_failure' => 'Unknown Failure',
        ];
        foreach ($warnings as $key => $label) {
            if (($info[$key] ?? 0) > 0) {
                $health .= " ($label)";
            }
        }

        return $name . $health;
    }
}
