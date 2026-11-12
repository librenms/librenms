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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Mac;
use LibreNMS\Util\Module;
use LibreNMS\Util\ModuleList;
use LibreNMS\Util\ModuleTestHelper;
use SnmpQuery;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\spin;
use function Laravel\Prompts\suggest;

class DevCollectSnmprec extends LnmsCommand
{
    protected $name = 'dev:collect-snmprec';
    protected bool $developer = true;
    private bool $isRequerying = false;

    public function __construct()
    {
        parent::__construct();

        $this->addArgument('device', mode: InputArgument::OPTIONAL);
        $this->addOption('model', 'M', InputOption::VALUE_REQUIRED);
        $this->addOption('modules', 'm', InputOption::VALUE_REQUIRED);
        $this->addOption('output', null, InputOption::VALUE_REQUIRED);
        $this->addOption('full', null, InputOption::VALUE_NONE);
        $this->setHelp(__('commands.dev:collect-snmprec.help'));
    }

    public function handle(): int
    {
        $output = $this->option('output') ?: null;

        if ($this->option('full')) {
            return $this->handleFullWalk($output);
        }

        // --output picks the file, so no model is used
        $model = $output === null ? $this->option('model') : null;
        if ($output === null && $model === null && ! $this->input->isInteractive()) {
            $this->error(__('commands.dev:collect-snmprec.model_required'));

            return 1;
        }

        if ($model !== null) {
            $model = strtolower(trim($model));
            if ($error = $this->validateModel($model)) {
                $this->error($error);

                return 1;
            }
        }

        $modules = $this->commaSeparatedOption('modules');

        // before any prompts
        foreach ($modules as $module) {
            $moduleName = explode('/', $module, 2)[0];
            if (! Module::exists($moduleName)) {
                $this->error(__('commands.dev:collect-snmprec.invalid_module', ['module' => $moduleName]));

                return 1;
            }
        }

        $device = $this->resolveDevice();
        if (! $device) {
            return 1;
        }

        DeviceCache::setPrimary($device->device_id);

        // modules collect only what the OS asks for, so a generic device would leave data out
        if ($device->os === 'generic') {
            $this->error(__('commands.dev:collect-snmprec.generic_device'));

            return 1;
        }

        $fixture = $output === null ? ModuleTestHelper::fixtureName($device->os, $model ?? $this->askModel($device->os)) : null;
        $snmprecFile = $output ?? ModuleTestHelper::snmprecFilepath($fixture);

        $this->line(__('commands.dev:collect-snmprec.modules', [
            'modules' => $modules === [] ? __('commands.dev:collect-snmprec.default_modules') : implode(',', $modules),
        ]));
        $this->printSavingAs($snmprecFile);
        $this->newLine();

        if (! $this->captureData($device, ModuleList::fromUserOverrides($modules), $snmprecFile)) {
            return 1;
        }

        $this->newLine();
        $this->info(__('commands.dev:collect-snmprec.verify_private_data'));
        if ($fixture !== null) {
            $this->info(__('commands.dev:collect-snmprec.next_step', ['fixture' => $fixture]));
        }

        return 0;
    }

    /**
     * Walk the whole device into --output. No modules run, so the OS and model are not used.
     */
    private function handleFullWalk(?string $snmprecFile): int
    {
        if ($this->option('modules') !== null) {
            $this->error('--full and --modules cannot be used together because a full walk does not run modules.');

            return 1;
        }

        if ($snmprecFile === null) {
            $this->error(__('commands.dev:collect-snmprec.full_requires_output'));

            return 1;
        }

        $device = $this->resolveDevice();
        if (! $device) {
            return 1;
        }

        $this->printSavingAs($snmprecFile);

        $data = $this->runWithProgress(
            fn () => SnmpQuery::device($device)->options(['-OUneb', '-Ih'])->walk('.'),
            __('commands.dev:collect-snmprec.capturing_data')
        );
        if ($data->getExitCode() !== 0) {
            $this->error(__('commands.dev:collect-snmprec.walk_failed', ['error' => $data->getErrorMessage()]));

            return 1;
        }

        if (! $this->saveSnmprec($snmprecFile, ['' => [$this->convertSnmpToSnmprec($data)]])) {
            return 1;
        }

        $this->newLine();
        $this->info(__('commands.dev:collect-snmprec.verify_private_data'));

        return 0;
    }

    /**
     * The device from the argument, or asked for when it is omitted
     */
    private function resolveDevice(): ?Device
    {
        $deviceSpec = $this->argument('device');

        if ($deviceSpec === null) {
            if (! $this->input->isInteractive()) {
                $this->error(__('commands.dev:collect-snmprec.device_required'));

                return null;
            }

            if (! Device::query()->exists()) {
                $this->error(__('commands.dev:collect-snmprec.no_devices'));

                return null;
            }

            $deviceSpec = suggest(
                label: __('commands.dev:collect-snmprec.ask_device'),
                options: fn (string $value) => $this->completeArgument('device', $value),
                required: true,
                validate: fn (string $value) => $this->findDevice($value) === null
                    ? __('commands.dev:collect-snmprec.device_not_found', ['device' => $value])
                    : null,
            );
        }

        $device = $this->findDevice($deviceSpec);

        if ($device === null) {
            $this->error(__('commands.dev:collect-snmprec.device_not_found', ['device' => $deviceSpec]));
        }

        return $device;
    }

    private function findDevice(string $deviceSpec): ?Device
    {
        try {
            $device = DeviceCache::get(trim($deviceSpec));
        } catch (\Exception) {
            return null;
        }

        return $device->exists ? $device : null;
    }

    private function printSavingAs(string $file): void
    {
        $this->line(__('commands.dev:collect-snmprec.saving_as', ['file' => $this->displayPath($file)]) . ' '
            . __(is_file($file) ? 'commands.dev:collect-snmprec.file_replaced' : 'commands.dev:collect-snmprec.file_new'));
    }

    private function displayPath(string $file): string
    {
        return Str::replaceStart(base_path() . '/', '', $file);
    }

    private function captureData(Device $device, ModuleList $moduleList, string $snmprecFile): bool
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
                new DiscoverDevice($device->device_id, $moduleList)->handle();
                new PollDevice($device->device_id, $moduleList)->handle();
            }, __('commands.dev:collect-snmprec.capturing_data'));
        } finally {
            config(['logging.channels.stdout.level' => $previous_level]);
            Event::forget(SnmpQueryExecuted::class);
        }

        return $this->saveSnmprec($snmprecFile, $snmprecDataByContext);
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
     * Write the snmprec file and its @context files. They are rewritten, never merged with data from earlier runs.
     *
     * @param  array<string, array<int, array<int, string>>>  $dataByContext
     * @return bool false when nothing was saved
     */
    private function saveSnmprec(string $baseFile, array $dataByContext): bool
    {
        $stem = str_replace('.snmprec', '', $baseFile);
        $results = [];
        foreach ($dataByContext as $context => $data) {
            $filename = $context === '' ? $baseFile : "$stem@$context.snmprec";

            // keyed by OID, the last value wins
            $newData = [];
            foreach (array_merge(...$data) as $line) {
                $newData[explode('|', $line, 2)[0]] = $line;
            }

            $this->cleanSnmprecData($newData);
            uksort($newData, fn ($a, $b) => $this->compareOids((string) $a, (string) $b));

            if ($newData !== []) {
                $results[$filename] = $newData;
            }
        }

        // keep the existing files when nothing was collected
        if ($results === []) {
            $this->error(__('commands.dev:collect-snmprec.no_data'));

            return false;
        }

        File::delete([$baseFile, ...(glob("$stem@*.snmprec") ?: [])]);

        foreach ($results as $filename => $lines) {
            if (file_put_contents($filename, implode(PHP_EOL, $lines) . PHP_EOL) === false) {
                $this->error(__('commands.dev:collect-snmprec.save_failed', ['file' => $this->displayPath($filename)]));

                return false;
            }

            $this->newLine();
            $this->info(__('commands.dev:collect-snmprec.saved_snmprec', ['file' => $this->displayPath($filename)]));
        }

        return true;
    }

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
            'model' => $this->filterCompletions($this->fixtureModels(), $current),
            'modules' => $this->filterCompletions(
                array_values(array_unique(array_merge(
                    array_keys(LibrenmsConfig::get('discovery_modules', [])),
                    array_keys(LibrenmsConfig::get('poller_modules', [])),
                ))),
                $current,
                commaDelimited: true
            ),
            default => null,
        };
    }

    private function validateModel(string $model): ?string
    {
        return preg_match('/^[a-z0-9.+-]*$/', $model) ? null : __('commands.dev:collect-snmprec.model_invalid');
    }

    /**
     * Ask for the model, suggesting the existing fixtures of the OS.
     * Input may be a full fixture name (linksys_wrt54g), the bare OS for the base fixture (linksys),
     * or just a model (wrt54g).
     */
    private function askModel(string $os): string
    {
        $fixtures = $this->fixtureNames()
            ->filter(fn ($name) => ModuleTestHelper::extractVariant($name)[0] === $os)
            ->values()
            ->all();

        if ($fixtures) {
            $this->line(__('commands.dev:collect-snmprec.existing_fixtures', ['os' => $os]));
            foreach ($fixtures as $fixture) {
                $this->line("- $fixture.snmprec");
            }
            $this->newLine();
        }

        $toModel = function (string $value) use ($os): string {
            // accept file names as listed (linksys_wrt54g.snmprec)
            $value = preg_replace('/\.snmprec$/i', '', trim($value));
            [$valueOs, $model] = ModuleTestHelper::extractVariant($value);

            return strtolower(strcasecmp($valueOs, $os) === 0 ? $model : $value);
        };

        // suggest bare models, with the OS name standing in for the base fixture
        $suggestions = array_map(function ($fixture) use ($os) {
            $model = ModuleTestHelper::extractVariant($fixture)[1];

            return $model === '' ? $os : $model;
        }, $fixtures);

        return $toModel(suggest(
            label: __('commands.dev:collect-snmprec.ask_model'),
            options: fn (string $value) => $this->filterCompletions($suggestions, $value)->all(),
            required: true,
            validate: fn (string $value) => $this->validateModel($toModel($value)),
            hint: __('commands.dev:collect-snmprec.ask_model_hint', ['os' => $os]),
        ));
    }

    /**
     * Fixture names in tests/snmpsim without extension or @context
     *
     * @return Collection<int, string>
     */
    private function fixtureNames(): Collection
    {
        return collect(glob(base_path('tests/snmpsim/*.snmprec')))
            ->map(fn ($file) => explode('@', basename($file, '.snmprec'), 2)[0])
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Models of the existing fixtures
     *
     * @return array<int, string>
     */
    private function fixtureModels(): array
    {
        return $this->fixtureNames()
            ->map(fn ($name) => ModuleTestHelper::extractVariant($name)[1])
            ->filter(fn ($model) => $model !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $values
     * @return Collection<int, string>
     */
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
            ->filter(fn ($value) => stripos((string) $value, $partial) === 0)
            ->map(fn ($value) => $prefix . $value)
            ->sort()
            ->values();
    }
}
