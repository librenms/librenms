<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use LibreNMS\Exceptions\InvalidModuleException;
use LibreNMS\Util\Debug;
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

        $this->addOption('os', 'o', InputOption::VALUE_OPTIONAL);
        $this->addOption('variant', 'r', InputOption::VALUE_OPTIONAL);
        $this->addOption('modules', 'm', InputOption::VALUE_OPTIONAL);
        $this->addOption('no-save', null, InputOption::VALUE_NONE);
        $this->addOption('file', 'f', InputOption::VALUE_OPTIONAL);
        $this->addOption('debug', 'd', InputOption::VALUE_NONE);
    }

    public function handle(): int
    {
        Debug::setVerbose(Debug::set((bool) $this->option('debug')));

        $os = $this->option('os');
        $variant = $this->option('variant');
        $modulesInput = $this->option('modules') ?: 'all';
        $modules = $modulesInput === 'all' ? [] : explode(',', $modulesInput);
        try {
            $osList = $this->findOsWithData($os, $variant, $modules);
        } catch (InvalidModuleException $e) {
            $this->error($e->getMessage());

            return 1;
        }

        if (empty($osList)) {
            $this->error('No matching snmprec(s) found.');

            return 1;
        }

        $outputFile = $this->option('file');
        if ($outputFile && count($osList) !== 1) {
            $this->error('Failed to create test data, --file option can be used with one OS/variant combination.');
            $this->error('Multiple combinations (' . count($osList) . ') found.');

            return 1;
        }

        $snmpsim = new Snmpsim;
        try {
            $this->startSnmpsim($snmpsim);

            foreach ($osList as [$targetOs, $targetVariant, $resolvedModules]) {
                $this->line("OS: $targetOs");
                $this->line("Module: $modulesInput");
                if ($targetVariant) {
                    $this->line("Variant: $targetVariant");
                }
                $this->newLine();

                LibrenmsConfig::reloadDefaults();
                $tester = new ModuleTestHelper(new ModuleList($resolvedModules), $targetOs, $targetVariant);
                $testData = $tester->generateTestData($snmpsim->ip, $snmpsim->port);

                if ($this->option('no-save')) {
                    $this->line(print_r($testData, true));
                } elseif ($testData !== null) {
                    $targetFile = $outputFile ?: $tester->getJsonFilepath();
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

    private function startSnmpsim(Snmpsim $snmpsim): void
    {
        $snmpsim->setupVenv(true);
        $snmpsim->start();
        $this->line('Waiting for snmpsim to initialize...');
        $snmpsim->waitForStartup();

        if (! $snmpsim->isRunning()) {
            throw new RuntimeException("Failed to start snmpsim, make sure it is installed, working, and there are no bad snmprec files.\n" . $snmpsim->getErrorOutput());
        }

        $this->newLine();
    }

    /**
     * @param array<string> $modules
     * @return array<string, array{string, string, array<string, bool|array<string>>}>
     * @throws InvalidModuleException
     */
    private function findOsWithData(?string $os, ?string $variant, array $modules): array
    {
        if ($os !== null && $variant !== null) {
            return [$os . '_' . $variant => [$os, $variant, ModuleList::fromUserOverrides($modules)->overrides]];
        }

        return ModuleTestHelper::findOsWithData($modules, $os);
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
