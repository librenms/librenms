<?php

namespace App\Console\Commands;

use App\Console\DynamicInputOption;
use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use Illuminate\Support\Collection;
use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Util\Module;
use LibreNMS\Util\ModuleList;
use LibreNMS\Util\ModuleTestHelper;
use LibreNMS\Util\Snmpsim;
use RuntimeException;
use Symfony\Component\Console\Input\InputOption;

class DevSaveTestData extends LnmsCommand
{
    protected $name = 'dev:save-test-data';
    protected bool $developer = true;

    public function __construct()
    {
        parent::__construct();

        $this->addOption('all', 'a', InputOption::VALUE_NONE);
        $this->addOption('os', 'o', InputOption::VALUE_REQUIRED);
        $this->addOption('variant', 'r', InputOption::VALUE_REQUIRED);
        $this->addOption('modules', 'm', InputOption::VALUE_REQUIRED);
        $this->addOption('output', null, InputOption::VALUE_REQUIRED);
        $this->setHelp(__('commands.dev:save-test-data.help'));
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
            $this->error('Specify --all or --os.');

            return 1;
        }
        if ($all && $os !== null) {
            $this->error('--all and --os cannot be used together.');

            return 1;
        }
        if ($variants !== [] && $os === null) {
            $this->error('--variant requires --os.');

            return 1;
        }

        $modulesInput = $this->option('modules');
        $modules = $modulesInput === null ? [] : array_values(array_filter(array_map('trim', explode(',', $modulesInput))));
        foreach ($modules as $module) {
            $moduleName = explode('/', $module, 2)[0];
            if (! Module::exists($moduleName)) {
                $this->error("Invalid module name: $moduleName");

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
            $scope = $os === null ? '' : " for OS '$os'";
            $this->error("No matching JSON test fixtures found$scope.");
            $this->line('OS-wide selection is based on existing tests/data/*.json files so detection-only snmprec files are not included.');
            if ($os !== null && $variants === []) {
                $this->line('To recreate a deleted fixture, specify its variant explicitly with --variant (use --variant= for the base OS fixture).');
            }

            return 1;
        }

        $output = $this->option('output');
        if ($output !== null && count($osList) !== 1) {
            $this->error('--output can only be used with one OS/variant combination.');
            $this->error('Multiple combinations (' . count($osList) . ') found.');

            return 1;
        }

        $snmpsim = new Snmpsim;
        try {
            $this->startSnmpsim($snmpsim, $output === '-');

            foreach ($osList as [$targetOs, $targetVariant, $resolvedModules]) {
                if ($output !== '-') {
                    $this->line("OS: $targetOs");
                    $this->line('Variant: ' . ($targetVariant === '' ? '(base)' : $targetVariant));
                    $this->line('Modules: ' . ($resolvedModules === [] ? 'configured defaults' : implode(',', array_keys($resolvedModules))));
                    $this->newLine();
                }

                LibrenmsConfig::reloadDefaults();
                $tester = new ModuleTestHelper(new ModuleList($resolvedModules), $targetOs, $targetVariant);
                $tester->setQuiet($output === '-');
                $testData = $tester->generateTestData($snmpsim->ip, $snmpsim->port);

                if ($testData !== null) {
                    if ($output === '-') {
                        $this->line(json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

                        continue;
                    }

                    $targetFile = $output ?: $tester->getJsonFilepath();
                    $this->persistTestData($testData, $targetFile);
                    $this->info("Saved to $targetFile" . PHP_EOL . 'Ready for testing!');
                }
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

    private function startSnmpsim(Snmpsim $snmpsim, bool $quiet = false): void
    {
        $snmpsim->setupVenv(true);
        $snmpsim->start();
        if (! $quiet) {
            $this->line('Waiting for snmpsim to initialize...');
        }
        $snmpsim->waitForStartup();

        if (! $snmpsim->isRunning()) {
            throw new RuntimeException("Failed to start snmpsim, make sure it is installed, working, and there are no bad snmprec files.\n" . $snmpsim->getErrorOutput());
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
    public function completeOptionValue(DynamicInputOption $option, string $current): ?Collection
    {
        return match ($option->getName()) {
            'os' => $this->filterCompletions($this->fixtureOsNames(), $current),
            'variant' => $this->filterCompletions($this->fixtureVariants(), $current, true),
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

    private function fixtureVariants(): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn ($file) => ModuleTestHelper::extractVariant($file)[1],
            glob(base_path('tests/data/*.json'))
        ), fn ($variant) => $variant !== '')));
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
        d_echo($testData);

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
