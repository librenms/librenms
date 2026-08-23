<?php

namespace App\Console\Commands;

use App\Console\DynamicInputOption;
use App\Console\LnmsCommand;
use App\Events\DeviceDiscovered;
use App\Events\DevicePolled;
use App\Events\DiscoveringModule;
use App\Events\ModuleDiscovered;
use App\Events\ModulePolled;
use App\Events\PollingModule;
use App\Facades\LibrenmsConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Laravel\Prompts\Progress;
use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Util\Module;
use LibreNMS\Util\ModuleList;
use LibreNMS\Util\ModuleTestHelper;
use LibreNMS\Util\Snmpsim;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function Laravel\Prompts\progress;

class DevGenerateTestData extends LnmsCommand
{
    protected $name = 'dev:generate-test-data';
    protected bool $developer = true;

    public function __construct()
    {
        parent::__construct();

        $this->addOption('all', 'a', InputOption::VALUE_NONE);
        $this->addOption('os', 'o', InputOption::VALUE_REQUIRED);
        $this->addOption('variant', 'r', InputOption::VALUE_REQUIRED);
        $this->addOption('modules', 'm', InputOption::VALUE_REQUIRED);
        $this->addOption('output', null, InputOption::VALUE_REQUIRED);
        $this->setHelp(__('commands.dev:generate-test-data.help'));
    }

    public function handle(): int
    {
        $os = $this->option('os');
        $all = (bool) $this->option('all');
        $variantInput = $this->option('variant');
        $variants = $variantInput === null
            ? []
            : array_values(array_unique(array_map('trim', explode(',', $variantInput))));
        if (! $all && $os === null) {
            $this->error(__('commands.dev:generate-test-data.scope_required'));

            return 1;
        }
        if ($all && $os !== null) {
            $this->error(__('commands.dev:generate-test-data.scope_conflict'));

            return 1;
        }
        if ($variants !== [] && $os === null) {
            $this->error(__('commands.dev:generate-test-data.variant_requires_os'));

            return 1;
        }

        $modulesInput = $this->option('modules');
        $modules = $modulesInput === null ? [] : array_values(array_filter(array_map('trim', explode(',', $modulesInput))));
        foreach ($modules as $module) {
            $moduleName = explode('/', $module, 2)[0];
            if (! Module::exists($moduleName)) {
                $this->error(__('commands.dev:generate-test-data.invalid_module', ['module' => $moduleName]));

                return 1;
            }
        }

        try {
            $osList = $this->findOsWithData($os, $variants, $modules);
        } catch (InvalidModuleException $e) {
            $this->error($e->getMessage());

            return 1;
        }

        if (empty($osList)) {
            $this->error($os === null
                ? __('commands.dev:generate-test-data.no_fixtures')
                : __('commands.dev:generate-test-data.no_fixtures_for_os', ['os' => $os]));
            $this->line(__('commands.dev:generate-test-data.fixture_selection_note'));
            if ($os !== null && $variants === []) {
                $this->line(__('commands.dev:generate-test-data.recreate_hint'));
            }

            return 1;
        }

        $output = $this->option('output');
        if ($output !== null && count($osList) !== 1) {
            $this->error(__('commands.dev:generate-test-data.output_single'));
            $this->error(__('commands.dev:generate-test-data.combinations_found', ['count' => count($osList)]));

            return 1;
        }
        $showProgress = count($osList) > 1
            && $output !== '-'
            && $this->output->getVerbosity() === OutputInterface::VERBOSITY_NORMAL;

        $snmpsim = new Snmpsim;
        try {
            $this->startSnmpsim($snmpsim, $output === '-');
            $progressBar = $showProgress ? progress(
                __('commands.dev:generate-test-data.progress.generating'),
                count($osList) * 2,
                hint: trans_choice('commands.dev:generate-test-data.progress.fixtures', count($osList), ['count' => count($osList)])
            ) : null;
            $fixtureName = '';
            if ($progressBar) {
                $this->registerProgressListeners($progressBar, $fixtureName);
            }
            $progressBar?->start();
            $saved = 0;

            foreach ($osList as [$targetOs, $targetVariant, $resolvedModules]) {
                $fixtureName = $targetVariant === '' ? $targetOs : $targetOs . '_' . $targetVariant;
                if ($output !== '-' && ! $showProgress) {
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
                $tester->setQuiet($output === '-' || ! $this->output->isVerbose());
                $testData = $tester->generateTestData($snmpsim->ip, $snmpsim->port);

                if ($testData !== null) {
                    if ($output === '-') {
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
                }
            }

            if ($progressBar) {
                $progressBar->label(__('commands.dev:generate-test-data.progress.generated'))->finish();
            }

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

        return 0;
    }

    private function registerProgressListeners(Progress $progressBar, string &$fixtureName): void
    {
        Event::listen(DiscoveringModule::class, function (DiscoveringModule $event) use ($progressBar, &$fixtureName): void {
            $progressBar->label(__('commands.dev:generate-test-data.progress.discovering_module', ['fixture' => $fixtureName, 'module' => $event->module]))->render();
        });
        Event::listen(ModuleDiscovered::class, function (ModuleDiscovered $event) use ($progressBar, &$fixtureName): void {
            $progressBar->label(__('commands.dev:generate-test-data.progress.discovered_module', ['fixture' => $fixtureName, 'module' => $event->module]))->render();
        });
        Event::listen(PollingModule::class, function (PollingModule $event) use ($progressBar, &$fixtureName): void {
            $progressBar->label(__('commands.dev:generate-test-data.progress.polling_module', ['fixture' => $fixtureName, 'module' => $event->module]))->render();
        });
        Event::listen(ModulePolled::class, function (ModulePolled $event) use ($progressBar, &$fixtureName): void {
            $progressBar->label(__('commands.dev:generate-test-data.progress.polled_module', ['fixture' => $fixtureName, 'module' => $event->module]))->render();
        });
        Event::listen(DeviceDiscovered::class, function () use ($progressBar, &$fixtureName): void {
            $progressBar->label(__('commands.dev:generate-test-data.progress.discovery_complete', ['fixture' => $fixtureName]))->advance();
        });
        Event::listen(DevicePolled::class, function () use ($progressBar, &$fixtureName): void {
            $progressBar->label(__('commands.dev:generate-test-data.progress.polling_complete', ['fixture' => $fixtureName]))->advance();
        });
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
     * @param  array<string>  $modules
     * @return array<string, array{string, string, array<string, bool|array<string>>}>
     *
     * @throws InvalidModuleException
     */
    private function findOsWithData(?string $os, array $variants, array $modules): array
    {
        if ($os !== null && $variants !== []) {
            $osList = [];
            $moduleOverrides = ModuleList::fromUserOverrides($modules)->overrides;
            foreach ($variants as $variant) {
                $baseName = $variant === '' ? $os : $os . '_' . $variant;
                $osList[$baseName] = [$os, $variant, $moduleOverrides];
            }

            return $osList;
        }

        $osList = ModuleTestHelper::findOsWithData($modules, $os);

        ksort($osList);

        return $osList;
    }

    /**
     * @return Collection<int, string>|null
     */
    public function completeOptionValue(DynamicInputOption $option, string $current, ?InputInterface $input = null): ?Collection
    {
        return match ($option->getName()) {
            'os' => $this->filterCompletions($this->fixtureOsNames(), $current),
            'variant' => $this->filterCompletions($this->fixtureVariants($this->selectedOs($input)), $current, true),
            'modules' => $this->filterCompletions($this->moduleNames(), $current, true),
            default => null,
        };
    }

    private function fixtureOsNames(): array
    {
        return array_values(array_unique(array_map(
            fn ($file) => ModuleTestHelper::extractVariant($file)[0],
            glob(base_path('tests/data/*.json'))
        )));
    }

    private function fixtureVariants(?string $os = null): array
    {
        return array_values(array_unique(array_filter(array_map(
            function ($file) use ($os) {
                [$fixtureOs, $variant] = ModuleTestHelper::extractVariant($file);

                return $os === null || $fixtureOs === $os ? $variant : null;
            },
            glob(base_path('tests/data/*.json'))
        ), fn ($variant) => $variant !== null && $variant !== '')));
    }

    private function selectedOs(?InputInterface $input): ?string
    {
        $os = $input?->getOption('os');

        return is_string($os) ? $os : null;
    }

    private function moduleNames(): array
    {
        return array_values(array_unique(array_merge(
            array_keys(LibrenmsConfig::get('discovery_modules', [])),
            array_keys(LibrenmsConfig::get('poller_modules', [])),
        )));
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
