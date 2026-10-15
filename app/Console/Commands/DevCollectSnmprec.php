<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\SelectsTestFixtures;
use App\Console\DynamicInputOption;
use App\Console\LnmsCommand;
use App\Events\SnmpQueryExecuted;
use App\Facades\DeviceCache;
use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;
use App\Models\Device;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Mac;
use LibreNMS\Util\ModuleList;
use LibreNMS\Util\ModuleTestHelper;
use SnmpQuery;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\spin;

class DevCollectSnmprec extends LnmsCommand
{
    use SelectsTestFixtures;

    protected $name = 'dev:collect-snmprec';
    protected bool $developer = true;
    private bool $isRequerying = false;

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
        if ($this->option('full')) {
            return $this->handleFullWalk();
        }

        $output = $this->option('output') ?: null;
        $variants = $this->variantOption();
        if ($variants !== null) {
            if ($error = count($variants) > 1 ? __('commands.dev:collect-snmprec.variant_single') : $this->validateVariant($variants[0])) {
                $this->error($error);

                return 1;
            }
        } elseif (! $this->input->isInteractive() && $output === null) {
            $this->error(__('commands.dev:collect-snmprec.variant_required'));

            return 1;
        }

        $modules = $this->commaSeparatedOption('modules');

        // before any prompts
        if ($error = $this->validateModules($modules)) {
            $this->error($error);

            return 1;
        }

        $device = $this->resolveDevice();
        if (! $device) {
            return 1;
        }

        DeviceCache::setPrimary($device->device_id);

        if ($device->os !== 'generic' && $this->option('os') !== null) {
            $this->error("--os cannot be used because the device is already detected as '{$device->os}'.");

            return 1;
        }

        $targetOs = $this->resolveTargetOs($device);
        if ($targetOs === null) {
            return 1;
        }

        // --output picks the file, so no variant is used
        $variant = $output === null ? ($variants[0] ?? $this->askVariant($targetOs)) : null;
        $snmprecFile = $output ?? ModuleTestHelper::snmprecFilepath($targetOs, $variant);

        $this->printFixtureSummary($targetOs, $variant, $modules);
        $this->printSavingAs(basename($snmprecFile), $snmprecFile);
        $this->newLine();

        try {
            $this->captureData(
                $device,
                ModuleList::fromUserOverrides($modules),
                $snmprecFile,
                (bool) $this->option('prefer-collected'),
            );

            $this->newLine();
            $this->info(__('commands.dev:collect-snmprec.verify_private_data'));
        } catch (InvalidModuleException $e) {
            $this->error($e->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * Walk the whole device into --output. No modules run, so the OS and variant are not used.
     */
    private function handleFullWalk(): int
    {
        if ($this->option('modules') !== null) {
            $this->error('--full and --modules cannot be used together because a full walk does not run modules.');

            return 1;
        }

        $snmprecFile = (string) $this->option('output');
        if ($snmprecFile === '') {
            $this->error(__('commands.dev:collect-snmprec.full_requires_output'));

            return 1;
        }

        $device = $this->resolveDevice();
        if (! $device) {
            return 1;
        }

        $this->printSavingAs($snmprecFile, $snmprecFile);

        $data = $this->runWithProgress(
            fn () => SnmpQuery::device($device)->options(['-OUneb', '-Ih'])->walk('.'),
            __('commands.dev:collect-snmprec.capturing_data')
        );
        if ($data->getExitCode() !== 0) {
            $this->error(__('commands.dev:collect-snmprec.walk_failed', ['error' => $data->getErrorMessage()]));

            return 1;
        }

        $this->saveSnmprec($snmprecFile, [$this->convertSnmpToSnmprec($data)], '', (bool) $this->option('prefer-collected'));

        $this->newLine();
        $this->info(__('commands.dev:collect-snmprec.verify_private_data'));

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

        if (! $device?->exists) {
            $this->error(__('commands.dev:collect-snmprec.device_not_found', ['device' => $deviceSpec]));

            return null;
        }

        return $device;
    }

    /**
     * The OS to save test data for: the detected OS, --os, or asked for when the device is generic
     */
    private function resolveTargetOs(Device $device): ?string
    {
        $osList = $this->getOsList();
        $targetOs = $device->os !== 'generic' ? $device->os : $this->option('os');
        if (! $targetOs && $this->input->isInteractive()) {
            $targetOs = $this->askOs($osList);
        }
        if (! $targetOs) {
            $this->error(__('commands.dev:collect-snmprec.os_required'));

            return null;
        }
        if (! $this->isValidOsName($targetOs)) {
            $this->error(__('commands.dev:collect-snmprec.invalid_os', ['os' => $targetOs]));

            return null;
        }

        $knownOs = $this->findKnownOs($targetOs, $osList);
        if ($knownOs !== null) {
            return $knownOs;
        }

        if (! $this->input->isInteractive()) {
            $this->warn(__('commands.dev:collect-snmprec.new_os', ['os' => $targetOs]));

            return $targetOs;
        }

        if (! confirm(__('commands.dev:collect-snmprec.confirm_new_os', ['os' => $targetOs]), default: false)) {
            $this->error(__('commands.dev:collect-snmprec.invalid_os', ['os' => $targetOs]));

            return null;
        }

        return $targetOs;
    }

    private function printSavingAs(string $label, string $file): void
    {
        $this->line(__('commands.dev:collect-snmprec.saving_as', ['file' => $label]) . (is_file($file) ? '' : ' (new)'));
    }

    private function captureData(Device $device, ModuleList $moduleList, string $snmprecFile, bool $preferCollected): void
    {
        $snmprecDataByContext = [];

        Event::listen(SnmpQueryExecuted::class, function (SnmpQueryExecuted $event) use (&$snmprecDataByContext, $device): void {
            if ($this->isRequerying || $event->response->getExitCode() !== 0) {
                return;
            }

            if ($this->output->isVerbose()) {
                foreach ($event->oids as $oid) {
                    $this->output->writeln($oid);
                }
            }

            $parsed = $this->convertSnmpToSnmprec($event->response);

            // Re-query if bulk response couldn't be parsed into numeric snmprec lines
            if (empty($parsed) && ! empty($event->oids)) {
                $this->isRequerying = true;
                $this->requeryOids($device, $event, $snmprecDataByContext);
                $this->isRequerying = false;

                return;
            }

            if (! empty($parsed)) {
                $snmprecDataByContext[$event->options->context][] = $parsed;
            }
        });

        $previous_level = config('logging.channels.stdout.level');
        if (! Debug::isEnabled()) {
            config(['logging.channels.stdout.level' => 'emergency']);
        }

        try {
            $this->runWithProgress(function () use ($device, $moduleList): void {
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
        $mibOption = ! empty($event->options->mibs) ? '+' . implode(':', $event->options->mibs) : 'ALL';
        $snmpOptions = ['-OUneb', '-Ih', '-m', $mibOption];

        foreach ($event->oids as $oid) {
            $query = SnmpQuery::device($device)
                ->options($snmpOptions)
                ->context($event->options->context)
                ->mibDir(implode(':', $event->options->mibDirs));

            $data = match ($event->method) {
                'snmpget' => $query->get($oid),
                'snmpgetnext' => $query->next($oid),
                default => $query->walk($oid),
            };

            if ($data->getExitCode() === 0) {
                $reParsed = $this->convertSnmpToSnmprec($data);
                if (! empty($reParsed)) {
                    $snmprecDataByContext[$event->options->context][] = $reParsed;
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

        foreach (explode(PHP_EOL, $snmpData->raw()) as $line) {
            if ($line === '') {
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

        if ($rawData === '' || $rawData === '""') {
            return "$oid|4|";
        }

        [$rawType, $data] = array_pad(explode(':', $rawData, 2), 2, '');
        if (str_starts_with($rawType, 'Wrong Type (should be ')) {
            [$rawType, $data] = explode(':', ltrim($data), 2);
        }

        $type = $this->getSnmprecType($rawType);
        if ($type === null) {
            Log::debug("Skipped line, bad type: $line");

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

        [$oid, $type, $data] = array_pad(explode('|', $result[$lastKey], 3), 3, '');

        $result[$lastKey] = $type === '4x'
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
            default => null,
        };
    }

    /**
     * @param  array<int, array<int, string>>  $data
     */
    private function saveSnmprec(string $baseFile, array $data, ?string $context, bool $preferCollected): void
    {
        $filename = $context
            ? str_replace('.snmprec', '', $baseFile) . "@$context.snmprec"
            : $baseFile;

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

            $cmp = (int) $part <=> (int) $bParts[$index];
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
            if ($line !== '') {
                [$oid] = explode('|', $line, 2);
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
                $parts = explode('|', $oidData, 3);
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
    public function completeOptionValue(DynamicInputOption $option, string $current, ?InputInterface $input = null): ?Collection
    {
        return match ($option->getName()) {
            'os' => $this->filterCompletions($this->getOsList(), $current),
            'variant' => $this->filterCompletions($this->getFixtureVariants(), $current),
            'modules' => $this->filterCompletions($this->moduleNames(), $current, commaDelimited: true),
            default => null,
        };
    }
}
