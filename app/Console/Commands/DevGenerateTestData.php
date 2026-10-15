<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\SelectsTestFixtures;
use App\Console\DynamicInputOption;
use App\Console\LnmsCommand;
use App\Events\DeviceDiscovered;
use App\Events\DevicePolled;
use App\Events\DiscoveringModule;
use App\Events\ModuleDiscovered;
use App\Events\ModulePolled;
use App\Events\PollingModule;
use App\Facades\LibrenmsConfig;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Laravel\Prompts\Progress;
use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Util\Module;
use LibreNMS\Util\ModuleList;
use LibreNMS\Util\ModuleTestHelper;
use LibreNMS\Util\Snmpsim;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\progress;

class DevGenerateTestData extends LnmsCommand
{
    use SelectsTestFixtures;

    protected $name = 'dev:generate-test-data';
    protected bool $developer = true;
    private string $currentFixture = '';

    public function __construct()
    {
        parent::__construct();

        $this->addArgument('os', InputArgument::OPTIONAL);
        $this->addOption('variant', 'r', InputOption::VALUE_REQUIRED);
        $this->addOption('modules', 'm', InputOption::VALUE_REQUIRED);
        $this->addOption('output', null, InputOption::VALUE_REQUIRED);
        $this->setHelp(__('commands.dev:generate-test-data.help'));
    }

    public function handle(): int
    {
        $output = $this->option('output');
        $toStdout = $output === '-';
        // don't prompt when the JSON goes to standard output
        $canAsk = $this->input->isInteractive() && ! $toStdout;

        $os = $this->argument('os');
        if ($os === null && $canAsk) {
            $os = $this->askOs(['all', ...$this->getFixtureOsList()]);
        }

        if (strcasecmp((string) $os, 'all') === 0) {
            $os = null;
        } elseif ($os === null) {
            $this->error(__('commands.dev:generate-test-data.scope_required'));

            return 1;
        } else {
            $os = $this->resolveOs($os);
            if ($os === null) {
                return 1;
            }
        }

        $variants = array_map(strtolower(...), $this->commaSeparatedOption('variant', filterEmpty: false));

        if ($variants !== [] && $os === null) {
            $this->error(__('commands.dev:generate-test-data.variant_requires_os'));

            return 1;
        }

        foreach ($variants as $variant) {
            if ($error = $this->validateVariant($variant)) {
                $this->error($error);

                return 1;
            }
        }

        $modules = $this->commaSeparatedOption('modules');

        foreach ($modules as $module) {
            $moduleName = explode('/', $module, 2)[0];
            if (! Module::exists($moduleName)) {
                $this->error(__('commands.dev:generate-test-data.invalid_module', ['module' => $moduleName]));

                return 1;
            }
        }

        $canAskVariant = $os !== null && $variants === [] && $canAsk;
        $fixtures = $canAskVariant ? $this->getExistingFixtures($os) : [];
        $hasSnmprec = $fixtures !== [];

        try {
            $osList = $this->findOsWithData($os, $variants, $modules);

            if ($hasSnmprec) {
                // all existing JSON fixtures are only offered when there are any and --output allows several
                $variant = $this->askVariant($os, $fixtures, existingOnly: true, extraOptions: $osList === [] || $output !== null ? [] : [
                    __('commands.dev:generate-test-data.ask_variant_all') => 'all',
                ]);
                if ($variant !== 'all') {
                    // without -m, keep the modules of the existing JSON
                    $osList = ($modules === [] ? Arr::only($osList, ModuleTestHelper::fixtureName($os, $variant)) : [])
                        ?: $this->findOsWithData($os, [$variant], $modules);
                }
            }
        } catch (InvalidModuleException $e) {
            $this->error($e->getMessage());

            return 1;
        }

        if (empty($osList)) {
            $this->error($os === null
                ? __('commands.dev:generate-test-data.no_fixtures')
                : __('commands.dev:generate-test-data.no_fixtures_for_os', ['os' => $os]));
            $this->line(__('commands.dev:generate-test-data.fixture_selection_note'));
            if ($canAskVariant && ! $hasSnmprec) {
                // asking was skipped because there is no snmprec file to pick
                $this->line(__('commands.dev:generate-test-data.no_snmprec', ['os' => $os]));
            } elseif ($os !== null && $variants === []) {
                $this->line(__('commands.dev:generate-test-data.recreate_hint'));
            }

            return 1;
        }

        if ($output !== null && count($osList) !== 1) {
            $this->error(__('commands.dev:generate-test-data.output_single'));
            $this->error(__('commands.dev:generate-test-data.combinations_found', ['count' => count($osList)]));

            return 1;
        }

        // skip fixtures without an snmprec file before snmpsim starts
        $failed = 0;
        foreach ($osList as $fixture => [$targetOs, $targetVariant]) {
            if (! is_file(ModuleTestHelper::snmprecFilepath($targetOs, $targetVariant))) {
                $this->error(__('commands.dev:generate-test-data.fixture_not_found', ['fixture' => $fixture]));
                unset($osList[$fixture]);
                $failed++;
            }
        }

        if ($osList === []) {
            return 1;
        }

        $showProgress = count($osList) > 1
            && ! $toStdout
            && $this->output->getVerbosity() === OutputInterface::VERBOSITY_NORMAL;

        $snmpsim = new Snmpsim;
        try {
            $this->startSnmpsim($snmpsim, $toStdout);

            $progressBar = $showProgress ? progress(
                __('commands.dev:generate-test-data.progress.generating'),
                count($osList) * 2,
                hint: trans_choice('commands.dev:generate-test-data.progress.fixtures', count($osList), ['count' => count($osList)])
            ) : null;

            if ($progressBar) {
                $this->registerProgressListeners($progressBar);
            }
            $progressBar?->start();
            $saved = 0;

            foreach ($osList as $fixture => [$targetOs, $targetVariant, $resolvedModules]) {
                $this->currentFixture = $fixture;
                if (! $toStdout && ! $showProgress) {
                    $this->line(__('commands.dev:generate-test-data.labels.os', ['os' => $targetOs]));
                    $this->line(__('commands.dev:generate-test-data.labels.variant', [
                        'variant' => $targetVariant === '' ? __('commands.dev:generate-test-data.labels.base') : $targetVariant,
                    ]));
                    $this->line(__('commands.dev:generate-test-data.labels.modules', [
                        'modules' => $resolvedModules === []
                            ? __('commands.dev:generate-test-data.labels.configured_defaults')
                            : implode(',', array_keys($resolvedModules)),
                    ]));
                    $this->newLine();
                }

                LibrenmsConfig::reloadDefaults();
                $tester = new ModuleTestHelper(new ModuleList($resolvedModules), $targetOs, $targetVariant);
                $tester->setQuiet($toStdout || ! $this->output->isVerbose());
                $testData = $tester->generateTestData($snmpsim->ip, $snmpsim->port);

                if ($testData !== null) {
                    if ($toStdout) {
                        $this->line(json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

                        continue;
                    }

                    $targetFile = $output ?: $tester->getJsonFilepath();
                    $this->persistTestData($testData, $targetFile);
                    if (! $showProgress) {
                        $this->info(__('commands.dev:generate-test-data.saved_to', ['file' => $targetFile]));
                        $this->newLine();
                    }
                    $saved++;
                } else {
                    $failed++;
                    $this->error(__('commands.dev:generate-test-data.generate_failed', ['fixture' => $fixture]));
                }
            }

            $progressBar?->label(__('commands.dev:generate-test-data.progress.generated'))->finish();

            if ($saved > 0) {
                if ($showProgress) {
                    $this->info(trans_choice('commands.dev:generate-test-data.generated_count', $saved, ['count' => $saved]));
                }
                $this->info(__('commands.dev:generate-test-data.ready'));
            }
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return 1;
        } finally {
            $snmpsim->stop();
            LibrenmsConfig::reload();
        }

        return $failed > 0 ? 1 : 0;
    }

    /**
     * A known OS with its case fixed, or the OS part of existing fixtures whose variant contains
     * an underscore (arubaos-cx_6300_vsf_stack is read as OS arubaos-cx_6300_vsf, variant stack)
     */
    private function resolveOs(string $os): ?string
    {
        if ($this->isValidOsName($os)) {
            $knownOs = $this->findKnownOs($os, $this->getOsList());
            if ($knownOs !== null) {
                return $knownOs;
            }

            // OS names that only exist in fixture names, like arubaos-cx_6300_vsf
            if (in_array($os, $this->getFixtureOsList(), true)) {
                return $os;
            }
        }

        $this->error(__('commands.dev:generate-test-data.invalid_os', ['os' => $os]));

        return null;
    }

    /**
     * OS names that have snmprec fixtures
     *
     * @return array<int, string>
     */
    private function getFixtureOsList(): array
    {
        return $this->getFixtureNames()
            ->map(fn ($name) => ModuleTestHelper::extractVariant($name)[0])
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @template TSteps of iterable<mixed>|int
     *
     * @param  Progress<TSteps>  $progressBar
     */
    private function registerProgressListeners(Progress $progressBar): void
    {
        Event::listen(DiscoveringModule::class, fn ($event) => $progressBar->label(__('commands.dev:generate-test-data.progress.discovering_module', ['fixture' => $this->currentFixture, 'module' => $event->module]))->render());
        Event::listen(ModuleDiscovered::class, fn ($event) => $progressBar->label(__('commands.dev:generate-test-data.progress.discovered_module', ['fixture' => $this->currentFixture, 'module' => $event->module]))->render());
        Event::listen(PollingModule::class, fn ($event) => $progressBar->label(__('commands.dev:generate-test-data.progress.polling_module', ['fixture' => $this->currentFixture, 'module' => $event->module]))->render());
        Event::listen(ModulePolled::class, fn ($event) => $progressBar->label(__('commands.dev:generate-test-data.progress.polled_module', ['fixture' => $this->currentFixture, 'module' => $event->module]))->render());
        Event::listen(DeviceDiscovered::class, fn () => $progressBar->label(__('commands.dev:generate-test-data.progress.discovery_complete', ['fixture' => $this->currentFixture]))->advance());
        Event::listen(DevicePolled::class, fn () => $progressBar->label(__('commands.dev:generate-test-data.progress.polling_complete', ['fixture' => $this->currentFixture]))->advance());
    }

    private function startSnmpsim(Snmpsim $snmpsim, bool $quiet = false): void
    {
        $snmpsim->setupVenv(true);
        $snmpsim->start();
        if (! $quiet) {
            $this->line(__('commands.dev:generate-test-data.waiting_for_snmpsim'));
        }
        $snmpsim->waitForStartup();

        if (! $snmpsim->isRunning()) {
            throw new RuntimeException(__('commands.dev:generate-test-data.snmpsim_failed', ['error' => $snmpsim->getErrorOutput()]));
        }

        if (! $quiet) {
            $this->newLine();
        }
    }

    /**
     * @param  array<string>  $variants
     * @param  array<string>  $modules
     * @return array<string, array{string, string, array<string, bool|array<string>>}>
     *
     * @throws InvalidModuleException
     */
    private function findOsWithData(?string $os, array $variants, array $modules): array
    {
        if ($os !== null && $variants !== []) {
            $moduleOverrides = ModuleList::fromUserOverrides($modules)->overrides;

            return collect($variants)
                ->mapWithKeys(fn ($variant) => [ModuleTestHelper::fixtureName($os, $variant) => [$os, $variant, $moduleOverrides]])
                ->all();
        }

        $osList = ModuleTestHelper::findOsWithData($modules, $os);
        ksort($osList);

        return $osList;
    }

    /**
     * @return array<int, string>|false
     */
    public function completeArgument(string $name, string $value, mixed $previous = null): array|false
    {
        if ($name === 'os') {
            return $this->filterCompletions(['all', ...$this->getFixtureOsList()], $value)->all();
        }

        return false;
    }

    /**
     * @return Collection<int, string>|null
     */
    public function completeOptionValue(DynamicInputOption $option, string $current, ?InputInterface $input = null): ?Collection
    {
        $os = $input?->getArgument('os');
        if (! is_string($os) || strcasecmp($os, 'all') === 0 || ! $this->isValidOsName($os)) {
            $os = null;
        }

        return match ($option->getName()) {
            'variant' => $this->filterCompletions($this->getFixtureVariants($os), $current, commaDelimited: true),
            'modules' => $this->filterCompletions($this->moduleNames(), $current, commaDelimited: true),
            default => null,
        };
    }

    /**
     * @param  array<string, array{discovery?: array<string, array<int, array<string, mixed>>>, poller?: array<string, array<int, array<string, mixed>>>}>  $testData
     */
    protected function persistTestData(array $testData, string $outputFile): void
    {
        $existingData = is_readable($outputFile)
            ? json_decode(file_get_contents($outputFile), true)
            : [];

        foreach ($testData as $module => $moduleData) {
            if (empty($moduleData['discovery']) && empty($moduleData['poller'])) {
                continue;
            }

            $existingData[$module] = isset($moduleData['discovery'], $moduleData['poller']) && $moduleData['discovery'] === $moduleData['poller']
                ? ['discovery' => $moduleData['discovery'], 'poller' => 'matches discovery']
                : $moduleData;
        }

        file_put_contents($outputFile, json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
    }
}
