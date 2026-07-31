<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Events\SnmpQueryExecuted;
use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;
use App\Models\Device;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Data\Source\SnmpResponse;
use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Mac;
use LibreNMS\Util\ModuleList;
use SnmpQuery;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DevCollectSnmprec extends LnmsCommand
{
    protected $name = 'dev:collect-snmprec';
    protected $developer = true;

    public function __construct()
    {
        parent::__construct();

        $this->addArgument('device', mode: InputArgument::REQUIRED);
        $this->addOption('variant', null, InputOption::VALUE_REQUIRED);
        $this->addOption('modules', 'm', InputOption::VALUE_OPTIONAL);
        $this->addOption('prefer-new', null, InputOption::VALUE_NONE);
        $this->addOption('os', 'o', InputOption::VALUE_OPTIONAL);
        $this->addOption('file', 'f', InputOption::VALUE_OPTIONAL);
        $this->addOption('debug', 'd', InputOption::VALUE_NONE);
        $this->addOption('full', null, InputOption::VALUE_NONE);
    }

    public function handle(): int
    {
        $deviceSpec = $this->argument('device');
        try {
            $device = DeviceCache::get($deviceSpec);
        } catch (\Exception $e) {
            $device = null;
        }

        if (! $device || ! $device->exists) {
            $this->error(__('commands.dev:collect-snmprec.device_not_found', ['device' => $deviceSpec]));

            return 1;
        }

        $variant = $this->option('variant');
        if ($variant === null) {
            $this->error(__('commands.dev:collect-snmprec.variant_required'));

            return 1;
        }

        if (Str::contains($variant, '_')) {
            $this->error(__('commands.dev:collect-snmprec.variant_underscore'));

            return 1;
        }

        if ($device->os !== 'generic') {
            $targetOs = $device->os;
        } else {
            $targetOs = $this->option('os');
            if (! $targetOs) {
                $this->error(__('commands.dev:collect-snmprec.os_required'));

                return 1;
            }
        }

        Debug::set((bool) $this->option('debug'));

        $modulesInput = $this->option('modules');
        if ($modulesInput !== null && $modulesInput !== '') {
            $modules = explode(',', $modulesInput);
        } else {
            $modulesInput = 'all';
            $modules = [];
        }

        $this->line("OS: $targetOs");
        $this->line("Module(s): $modulesInput");
        if ($variant !== '') {
            $this->line("Variant: $variant");
        }
        $this->newLine();

        try {
            $moduleList = ModuleList::fromUserOverrides($modules);
            $preferNew = (bool) $this->option('prefer-new');
            $full = (bool) $this->option('full');
            $snmprecFile = $this->getSnmprecFilePath($targetOs, $variant);

            $this->captureData($device, $moduleList, $snmprecFile, $preferNew, $full);

            $this->newLine();
            $this->info(__('commands.dev:collect-snmprec.verify_private_data'));
        } catch (InvalidModuleException $e) {
            $this->error($e->getMessage());

            return 1;
        }

        return 0;
    }

    private function getSnmprecFilePath(string $os, string $variant): string
    {
        if ($customFile = $this->option('file')) {
            return $customFile;
        }

        $variantSuffix = strtolower($variant) ? '_' . strtolower($variant) : '';
        $installDir = LibrenmsConfig::get('install_dir');

        return "$installDir/tests/snmpsim/" . $os . $variantSuffix . '.snmprec';
    }

    private function captureData(Device $device, ModuleList $moduleList, string $snmprecFile, bool $preferNew, bool $full): void
    {
        if ($full) {
            $this->output->write(__('commands.dev:collect-snmprec.capturing_data') . ' .');
            $data = SnmpQuery::options(['-OUneb', '-Ih'])->walk('.');
            if ($data->getExitCode() === 0) {
                $snmprecData = [$this->convertSnmpToSnmprec($data)];
                $this->saveSnmprec($snmprecFile, $snmprecData, '', $preferNew);
            }

            return;
        }

        $snmprecDataByContext = [];

        // Always capture sysDescr.0 and sysObjectID.0 first
        foreach (['sysDescr.0', 'sysObjectID.0'] as $oid) {
            $data = SnmpQuery::options(['-OUneb', '-Ih', '-m', '+SNMPv2-MIB'])->get($oid);
            if ($data->getExitCode() === 0) {
                $snmprecDataByContext[''][] = $this->convertSnmpToSnmprec($data);
            }
        }

        $isRequerying = false;
        $listener = function (SnmpQueryExecuted $event) use (&$snmprecDataByContext, $device, &$isRequerying): void {
            if ($isRequerying || $event->response->getExitCode() !== 0) {
                return;
            }

            $parsed = $this->convertSnmpToSnmprec($event->response);

            // If the captured response could not be parsed into snmprec lines (e.g. non-numeric OID format or missing type info),
            // re-query the device with optimal options (-OUneb -Ih -m +MIB)
            if (empty($parsed) && ! empty($event->oids)) {
                $isRequerying = true;

                $mibOption = ! empty($event->mibs) ? '+' . implode(':', $event->mibs) : 'ALL';
                $snmpOptions = ['-OUneb', '-Ih', '-m', $mibOption];
                $mibDir = $event->mibDir;

                foreach ($event->oids as $oid) {
                    $query = SnmpQuery::device($device)
                        ->options($snmpOptions)
                        ->context($event->context)
                        ->mibDir($mibDir);

                    $data = match ($event->method) {
                        'snmpget' => $query->get($oid),
                        'snmpgetnext' => $query->next($oid),
                        default => $query->walk($oid),
                    };

                    if ($data->getExitCode() === 0) {
                        $reParsed = $this->convertSnmpToSnmprec($data);
                        if (! empty($reParsed)) {
                            $snmprecDataByContext[$event->context][] = $reParsed;
                        }
                    }
                }

                $isRequerying = false;

                return;
            }

            if (! empty($parsed)) {
                $snmprecDataByContext[$event->context][] = $parsed;
            }

            foreach ($event->oids as $oid) {
                $this->output->write(' ' . $oid);
            }
        };

        $this->output->write(__('commands.dev:collect-snmprec.capturing_data'));

        DeviceCache::setPrimary($device->device_id);

        Event::listen(SnmpQueryExecuted::class, $listener);

        (new DiscoverDevice($device->device_id, $moduleList))->handle();
        (new PollDevice($device->device_id, $moduleList))->handle();

        Event::forget(SnmpQueryExecuted::class);

        foreach ($snmprecDataByContext as $context => $snmprecData) {
            $this->saveSnmprec($snmprecFile, $snmprecData, $context, $preferNew);
        }
    }

    private function convertSnmpToSnmprec(SnmpResponse $snmpData): array
    {
        $result = [];
        foreach (explode(PHP_EOL, $snmpData->getRawWithoutBadLines()) as $line) {
            if (empty($line)) {
                continue;
            }

            if (preg_match('/^\.[.\d]+ =/', $line)) {
                [$oid, $rawData] = explode(' =', $line, 2);
                $oid = ltrim($oid, '.');
                $rawData = trim($rawData);

                if (empty($rawData) || $rawData == '""') {
                    $result[] = "$oid|4|";
                } else {
                    [$rawType, $data] = array_pad(explode(':', $rawData, 2), 2, '');
                    if (Str::startsWith($rawType, 'Wrong Type (should be ')) {
                        [$rawType, $data] = explode(':', ltrim($data), 2);
                    }

                    $type = $this->getSnmprecType($rawType);

                    if ($type === null) {
                        Log::debug('Skipped line, bad type: ' . $line);
                        continue;
                    }

                    $data = ltrim($data, ' ');
                    if (Str::startsWith($data, '"') && Str::endsWith($data, '"')) {
                        $data = stripslashes(substr($data, 1, -1));
                    }

                    if ($type == '6') {
                        $data = ltrim($data, '.');
                    } elseif ($type == '4x') {
                        $data = str_replace(' ', '', $data);
                    } elseif ($type == '67') {
                        preg_match('/\((\d+)\)/', $data, $match);
                        $data = $match[1] ?? $data;
                    }

                    $result[] = "$oid|$type|$data";
                }
            } else {
                $lastKey = array_key_last($result);
                if ($lastKey === null) {
                    continue;
                }

                [$oid, $type, $data] = array_pad(explode('|', $result[$lastKey], 3), 3, '');
                if ($type == '4x') {
                    $result[$lastKey] .= bin2hex(PHP_EOL . $line);
                } else {
                    $result[$lastKey] = "$oid|4x|" . bin2hex($data . PHP_EOL . $line);
                }
            }
        }

        return $result;
    }

    private function getSnmprecType(string $text): ?string
    {
        return match ($text) {
            'STRING', 'OCTET STRING', 'BITS', 'Network Address' => '4',
            'OID', 'OBJECT IDENTIFIER' => '6',
            'Hex-STRING' => '4x',
            'Timeticks' => '67',
            'INTEGER', 'Integer32' => '2',
            'NULL' => '5',
            'IpAddress' => '64',
            'Counter32' => '65',
            'Gauge32' => '66',
            'Opaque' => '68',
            'Counter64' => '70',
            default => null
        };
    }

    private function saveSnmprec(string $baseFile, array $data, ?string $context, bool $preferNew): void
    {
        $filename = $baseFile;

        if ($context) {
            $filename = str_replace('.snmprec', '', $filename) . "@$context.snmprec";
        }

        $existingData = is_file($filename) ? $this->indexSnmprec(explode(PHP_EOL, file_get_contents($filename))) : [];

        $newData = [];
        foreach ($data as $part) {
            $newData = array_merge($newData, $this->indexSnmprec($part));
        }

        $this->cleanSnmprecData($newData);

        $results = $preferNew ? array_merge($existingData, $newData) : array_merge($newData, $existingData);

        uksort($results, function ($a, $b) {
            $aParts = explode('.', (string) $a);
            $bParts = explode('.', (string) $b);

            foreach ($aParts as $index => $aPart) {
                if (! isset($bParts[$index])) {
                    return 1;
                }

                if ($aPart > $bParts[$index]) {
                    return 1;
                } elseif ($aPart < $bParts[$index]) {
                    return -1;
                }
            }

            return count($aParts) <=> count($bParts);
        });

        $output = implode(PHP_EOL, $results) . PHP_EOL;

        if (empty($results)) {
            $this->info(__('commands.dev:collect-snmprec.no_data', ['file' => $filename]));
        } else {
            $this->newLine();
            $this->info(__('commands.dev:collect-snmprec.saved_snmprec', ['file' => $filename]));
            file_put_contents($filename, $output);
        }
    }

    private function indexSnmprec(array $snmprecData): array
    {
        $result = [];

        foreach ($snmprecData as $line) {
            if (! empty($line)) {
                [$oid] = explode('|', (string) $line, 2);
                $result[$oid] = $line;
            }
        }

        return $result;
    }

    private function cleanSnmprecData(array &$data): void
    {
        $privateOids = [
            '1.3.6.1.2.1.1.6.0',
            '1.3.6.1.2.1.1.4.0',
            '1.3.6.1.2.1.1.5.0',
        ];

        foreach ($privateOids as $oid) {
            if (isset($data[$oid])) {
                $parts = explode('|', $data[$oid], 3);
                $parts[2] = $parts[1] === '4' ? '<private>' : '3C707269766174653E';
                $data[$oid] = implode('|', $parts);
            }
        }

        foreach ($data as $oid => $oidData) {
            if (str_starts_with((string) $oid, '1.3.6.1.2.1.2.2.1.6.')) {
                $parts = explode('|', (string) $oidData, 3);
                $mac = Mac::parse($parts[2])->hex();
                if ($mac) {
                    $parts[2] = $mac;
                    $parts[1] = '4x';
                    $data[$oid] = implode('|', $parts);
                }
            }
        }
    }

    public function completeArgument($name, $value): array|false
    {
        if ($name === 'device') {
            return Device::query()
                ->when($value, fn ($query) => $query->where('hostname', 'like', "$value%")->orWhere('device_id', $value))
                ->orderBy('hostname')
                ->limit(25)
                ->pluck('hostname')
                ->all();
        }

        return false;
    }
}
