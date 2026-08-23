<?php

namespace App\Console\Commands;

use App\Console\DynamicInputOption;
use App\Console\LnmsCommand;
use App\Events\SnmpQueryExecuted;
use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;
use App\Models\Device;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use LibreNMS\Data\Source\SnmpResponse;
use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Mac;
use LibreNMS\Util\Module;
use LibreNMS\Util\ModuleList;
use LibreNMS\Util\ModuleTestHelper;
use SnmpQuery;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function Laravel\Prompts\spin;

class DevCollectSnmprec extends LnmsCommand
{
    protected $name = 'dev:collect-snmprec';
    protected bool $developer = true;

    public function __construct()
    {
        parent::__construct();

        $this->addArgument('device', mode: InputArgument::REQUIRED);
        $this->addOption('variant', 'r', InputOption::VALUE_REQUIRED);
        $this->addOption('modules', 'm', InputOption::VALUE_REQUIRED);
        $this->addOption('prefer-collected', null, InputOption::VALUE_NONE);
        $this->addOption('os', 'o', InputOption::VALUE_REQUIRED);
        $this->addOption('output', null, InputOption::VALUE_REQUIRED);
        $this->addOption('full', null, InputOption::VALUE_NONE);
        $this->setHelp(__('commands.dev:collect-snmprec.help'));
    }

    public function handle(): int
    {
        $variant = $this->option('variant');
        if ($variant === null) {
            $this->error(__('commands.dev:collect-snmprec.variant_required'));

            return 1;
        }
        $variant = trim($variant);

        if (str_contains($variant, '_')) {
            $this->error(__('commands.dev:collect-snmprec.variant_underscore'));

            return 1;
        }
        if (str_contains($variant, ',')) {
            $this->error(__('commands.dev:collect-snmprec.variant_single'));

            return 1;
        }

        if ($this->option('full') && $this->option('modules') !== null) {
            $this->error('--full and --modules cannot be used together because a full walk does not run modules.');

            return 1;
        }

        $device = $this->resolveDevice();
        if (! $device) {
            return 1;
        }

        if ($device->os !== 'generic' && $this->option('os') !== null) {
            $this->error("--os cannot be used because the device is already detected as '{$device->os}'.");

            return 1;
        }

        $targetOs = $device->os !== 'generic' ? $device->os : $this->option('os');
        if (! $targetOs) {
            $this->error(__('commands.dev:collect-snmprec.os_required'));

            return 1;
        }

        $modulesInput = $this->option('modules');
        $modules = ($modulesInput === null || $modulesInput === '')
            ? []
            : array_values(array_filter(array_map('trim', explode(',', $modulesInput))));
        $modulesInput = $modulesInput ?: 'configured defaults';
        foreach ($modules as $module) {
            $moduleName = explode('/', $module, 2)[0];
            if (! Module::exists($moduleName)) {
                $this->error("Invalid module name: $moduleName");

                return 1;
            }
        }

        $this->line("OS: $targetOs");
        $this->line('Variant: ' . ($variant === '' ? '(base; explicitly selected)' : $variant));
        $this->line("Modules: $modulesInput");
        $this->newLine();

        try {
            $moduleList = ModuleList::fromUserOverrides($modules);
            $snmprecFile = $this->getSnmprecFilePath($targetOs, $variant);

            $this->captureData(
                $device,
                $moduleList,
                $snmprecFile,
                (bool) $this->option('prefer-collected'),
                (bool) $this->option('full'),
            );

            $this->newLine();
            $this->info(__('commands.dev:collect-snmprec.verify_private_data'));
        } catch (InvalidModuleException $e) {
            $this->error($e->getMessage());

            return 1;
        }

        return 0;
    }

    private function resolveDevice(): ?Device
    {
        $deviceSpec = $this->argument('device');

        try {
            $device = DeviceCache::get($deviceSpec);
        } catch (\Exception) {
            $device = null;
        }

        if (! $device || ! $device->exists) {
            $this->error(__('commands.dev:collect-snmprec.device_not_found', ['device' => $deviceSpec]));

            return null;
        }

        return $device;
    }

    private function getSnmprecFilePath(string $os, string $variant): string
    {
        if ($customFile = $this->option('output')) {
            return (string) $customFile;
        }

        $variantSuffix = strtolower($variant) ? '_' . strtolower($variant) : '';
        $installDir = LibrenmsConfig::get('install_dir');

        return "$installDir/tests/snmpsim/" . $os . $variantSuffix . '.snmprec';
    }

    private function captureData(Device $device, ModuleList $moduleList, string $snmprecFile, bool $preferCollected, bool $full): void
    {
        if ($full) {
            $data = $this->runWithProgress(
                fn () => SnmpQuery::options(['-OUneb', '-Ih'])->walk('.'),
                __('commands.dev:collect-snmprec.capturing_data')
            );
            if ($data->getExitCode() === 0) {
                $this->saveSnmprec($snmprecFile, [$this->convertSnmpToSnmprec($data)], '', $preferCollected);
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
            if ($isRequerying || $event->response->getExitCode() !== 0) { // @phpstan-ignore booleanOr.leftAlwaysFalse
                return;
            }

            foreach ($event->oids as $oid) {
                if ($this->output->isVerbose()) {
                    $this->output->writeln($oid);
                }
            }

            $parsed = $this->convertSnmpToSnmprec($event->response);

            // If the captured response could not be parsed into snmprec lines (e.g. non-numeric OID format or missing type info),
            // re-query the device with optimal options (-OUneb -Ih -m +MIB)
            if (empty($parsed) && ! empty($event->oids)) {
                $isRequerying = true;
                $this->requeryOids($device, $event, $snmprecDataByContext);
                $isRequerying = false;

                return;
            }

            if (! empty($parsed)) {
                $snmprecDataByContext[$event->context][] = $parsed;
            }
        };

        DeviceCache::setPrimary($device->device_id);

        Event::listen(SnmpQueryExecuted::class, $listener);

        $previous_level = config('logging.channels.stdout.level');
        if (! Debug::isEnabled()) {
            config(['logging.channels.stdout.level' => 'emergency']);
        }

        try {
            $this->runWithProgress(function () use ($device, $moduleList) {
                (new DiscoverDevice($device->device_id, $moduleList))->handle();
                (new PollDevice($device->device_id, $moduleList))->handle();
            }, __('commands.dev:collect-snmprec.capturing_data'));
        } finally {
            config(['logging.channels.stdout.level' => $previous_level]);
            Event::forget(SnmpQueryExecuted::class);
        }

        foreach ($snmprecDataByContext as $context => $snmprecData) {
            $this->saveSnmprec($snmprecFile, $snmprecData, $context, $preferCollected);
        }
    }

    private function runWithProgress(\Closure $callback, string $message): mixed
    {
        if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_QUIET) {
            return $callback();
        }

        if ($this->output->isVerbose()) {
            $this->line($message);

            return $callback();
        }

        return spin($callback, $message);
    }

    /**
     * Re-query OIDs one at a time with explicit MIB options when the bulk response couldn't be parsed.
     *
     * @param  array<string, array<int, array<int, string>>>  $snmprecDataByContext
     */
    private function requeryOids(Device $device, SnmpQueryExecuted $event, array &$snmprecDataByContext): void
    {
        $mibOption = ! empty($event->mibs) ? '+' . implode(':', $event->mibs) : 'ALL';
        $snmpOptions = ['-OUneb', '-Ih', '-m', $mibOption];

        foreach ($event->oids as $oid) {
            $query = SnmpQuery::device($device)
                ->options($snmpOptions)
                ->context($event->context)
                ->mibDir($event->mibDir);

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
    }

    /**
     * @return array<int, string>
     */
    private function convertSnmpToSnmprec(SnmpResponse $snmpData): array
    {
        $result = [];

        foreach (explode(PHP_EOL, $snmpData->getRawWithoutBadLines()) as $line) {
            if (empty($line)) {
                continue;
            }

            if (preg_match('/^\.[.\d]+ =/', $line)) {
                $parsed = $this->parseOidLine($line);
                if ($parsed !== null) {
                    $result[] = $parsed;
                }
            } else {
                $this->appendContinuationLine($result, $line);
            }
        }

        return $result;
    }

    /**
     * Parse a single "OID = TYPE: value" snmpwalk line into an "oid|type|value" snmprec line.
     */
    private function parseOidLine(string $line): ?string
    {
        [$oid, $rawData] = explode(' =', $line, 2);
        $oid = ltrim($oid, '.');
        $rawData = trim($rawData);

        if (empty($rawData) || $rawData == '""') {
            return "$oid|4|";
        }

        [$rawType, $data] = array_pad(explode(':', $rawData, 2), 2, '');
        if (str_starts_with($rawType, 'Wrong Type (should be ')) {
            [$rawType, $data] = explode(':', ltrim($data), 2);
        }

        $type = $this->getSnmprecType($rawType);
        if ($type === null) {
            Log::debug('Skipped line, bad type: ' . $line);

            return null;
        }

        $data = ltrim($data, ' ');
        if (str_starts_with($data, '"') && str_ends_with($data, '"')) {
            $data = stripslashes(substr($data, 1, -1));
        }

        $data = match ($type) {
            '6' => ltrim($data, '.'),
            '4x' => str_replace(' ', '', $data),
            '67' => preg_match('/\((\d+)\)/', $data, $ticks) ? $ticks[1] : $data,
            default => $data,
        };

        return "$oid|$type|$data";
    }

    /**
     * Append a wrapped line (no leading OID) onto the previous result entry as hex-encoded data.
     *
     * @param  array<int, string>  $result
     */
    private function appendContinuationLine(array &$result, string $line): void
    {
        $lastKey = array_key_last($result);
        if ($lastKey === null) {
            return;
        }

        [$oid, $type, $data] = array_pad(explode('|', (string) $result[$lastKey], 3), 3, '');

        $result[$lastKey] = $type == '4x'
            ? $result[$lastKey] . bin2hex(PHP_EOL . $line)
            : "$oid|4x|" . bin2hex($data . PHP_EOL . $line);
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

    /**
     * @param  array<int, array<int, string>>  $data
     */
    private function saveSnmprec(string $baseFile, array $data, ?string $context, bool $preferCollected): void
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

        $results = $preferCollected ? array_merge($existingData, $newData) : array_merge($newData, $existingData);

        uksort($results, fn ($a, $b) => $this->compareOids((string) $a, (string) $b));

        if (empty($results)) {
            $this->info(__('commands.dev:collect-snmprec.no_data', ['file' => $filename]));

            return;
        }

        $this->newLine();
        $this->info(__('commands.dev:collect-snmprec.saved_snmprec', ['file' => $filename]));
        file_put_contents($filename, implode(PHP_EOL, $results) . PHP_EOL);
    }

    /**
     * Compare two dotted-decimal OIDs numerically, segment by segment.
     */
    private function compareOids(string $a, string $b): int
    {
        $aParts = explode('.', $a);
        $bParts = explode('.', $b);

        foreach ($aParts as $index => $part) {
            if (! isset($bParts[$index])) {
                return 1;
            }

            $cmp = $part <=> $bParts[$index];
            if ($cmp !== 0) {
                return $cmp;
            }
        }

        return count($aParts) <=> count($bParts);
    }

    /**
     * @param  array<int, string>  $snmprecData
     * @return array<string, string>
     */
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

    /**
     * @param  array<string, string>  $data
     */
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

    /**
     * @param  string  $name
     * @param  string  $value
     * @return array<int, string>|false
     */
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

    /**
     * @return Collection<int, string>|null
     */
    public function completeOptionValue(DynamicInputOption $option, string $current): ?Collection
    {
        return match ($option->getName()) {
            'os' => $this->filterCompletions(array_map(
                fn ($file) => basename($file, '.yaml'),
                glob(resource_path('definitions/os_detection/*.yaml'))
            ), $current),
            'variant' => $this->filterCompletions(array_values(array_unique(array_filter(array_map(
                fn ($file) => ModuleTestHelper::extractVariant(basename($file, '.snmprec'))[1],
                glob(base_path('tests/snmpsim/*.snmprec'))
            )))), $current),
            'modules' => $this->filterCompletions(array_values(array_unique(array_merge(
                array_keys(LibrenmsConfig::get('discovery_modules', [])),
                array_keys(LibrenmsConfig::get('poller_modules', [])),
            ))), $current, true),
            default => null,
        };
    }

    private function filterCompletions(array $values, string $current, bool $commaDelimited = false): Collection
    {
        $prefix = '';
        $partial = $current;
        if ($commaDelimited && str_contains($current, ',')) {
            $position = strrpos($current, ',') + 1;
            $prefix = substr($current, 0, $position);
            $partial = substr($current, $position);
        }

        return collect($values)
            ->filter(fn ($value) => str_starts_with((string) $value, $partial))
            ->map(fn ($value) => $prefix . $value)
            ->sort()
            ->values();
    }
}
