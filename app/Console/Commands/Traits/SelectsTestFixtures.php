<?php

namespace App\Console\Commands\Traits;

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Collection;
use LibreNMS\Util\ModuleTestHelper;

use function Laravel\Prompts\suggest;

/**
 * OS and variant selection for commands working on tests/snmpsim fixtures.
 * Messages are read from commands.<command name>.<key>.
 */
trait SelectsTestFixtures
{
    private function fixtureTrans(string $key, array $replace = []): string
    {
        return __('commands.' . $this->getName() . '.' . $key, $replace);
    }

    /**
     * @return array<int, string>
     */
    private function getOsList(): array
    {
        return array_map(fn ($file) => basename($file, '.yaml'), glob(resource_path('definitions/os_detection/*.yaml')));
    }

    /**
     * The known OS matching $os case-insensitively, with its correct case (ies52xxM, polycomLens)
     *
     * @param  array<int, string>  $osList
     */
    private function findKnownOs(string $os, array $osList): ?string
    {
        return collect($osList)->first(fn ($known) => strcasecmp($known, $os) === 0);
    }

    private function isValidOsName(string $os): bool
    {
        return (bool) preg_match('/^[a-z0-9_.+-]+$/i', $os);
    }

    private function validateVariant(string $variant): ?string
    {
        if (str_contains($variant, '_')) {
            return $this->fixtureTrans('variant_underscore');
        }

        if (! preg_match('/^[a-z0-9.+-]*$/i', $variant)) {
            return $this->fixtureTrans('variant_invalid');
        }

        return null;
    }

    /**
     * @param  array<int, string>  $osList
     */
    private function askOs(array $osList): string
    {
        return trim(suggest(
            label: $this->fixtureTrans('ask_os'),
            options: fn (string $value) => $this->filterCompletions($osList, $value)->all(),
            required: true,
        ));
    }

    /**
     * Ask for the fixture to use, suggesting existing fixtures and, unless $existingOnly, accepting a new variant.
     * Input may be a full fixture name (linksys_wrt54g), the bare OS for the base fixture (linksys),
     * or just a variant (wrt54g).
     *
     * @param  array<int, string>|null  $fixtures  existing fixtures of $os, looked up when null
     * @param  array<string, string>  $extraOptions  label => value of choices that are not variants, suggested first;
     *                                               the label or the value returns the value
     */
    private function askVariant(string $os, ?array $fixtures = null, bool $existingOnly = false, array $extraOptions = []): string
    {
        $fixtures ??= $this->getExistingFixtures($os);

        if ($fixtures) {
            $this->line($this->fixtureTrans('existing_fixtures', ['os' => $os]));
            foreach ($fixtures as $fixture) {
                $this->line("- $fixture.snmprec");
            }
            $this->newLine();
        }

        $toVariant = function (string $value) use ($os): string {
            // accept file names as listed (linksys_wrt54g.snmprec)
            $value = preg_replace('/\.snmprec$/i', '', trim($value));
            if (strcasecmp($value, $os) === 0) {
                return '';
            }

            [$valueOs, $variant] = ModuleTestHelper::extractVariant($value);

            return strtolower(strcasecmp($valueOs, $os) === 0 ? $variant : $value);
        };
        $toExtra = fn (string $value): ?string => collect($extraOptions)
            ->first(fn ($extra, $label) => strcasecmp(trim($value), $label) === 0 || strcasecmp(trim($value), $extra) === 0);

        // suggest bare variants, with the OS name standing in for the base fixture
        $variants = array_map($toVariant, $fixtures);
        $suggestions = array_map(fn ($variant) => $variant === '' ? $os : $variant, $variants);

        $answer = suggest(
            label: $this->fixtureTrans('ask_variant'),
            options: fn (string $value) => [
                ...$this->filterCompletions(array_keys($extraOptions), $value),
                ...$this->filterCompletions($suggestions, $value),
            ],
            required: true,
            validate: function (string $value) use ($toVariant, $toExtra, $variants, $existingOnly): ?string {
                if ($toExtra($value) !== null) {
                    return null;
                }

                $variant = $toVariant($value);

                return $this->validateVariant($variant)
                    ?? ($existingOnly && ! in_array($variant, $variants, true) ? $this->fixtureTrans('fixture_not_found', ['fixture' => trim($value)]) : null);
            },
            hint: $this->fixtureTrans('ask_variant_hint', ['os' => $os]),
        );

        return $toExtra($answer) ?? $toVariant($answer);
    }

    /**
     * Existing fixture names (without extension or @context) for an OS
     *
     * @return array<int, string>
     */
    private function getExistingFixtures(string $os): array
    {
        // $name === $os keeps the base fixture of a new OS with an underscore (no yaml for extractVariant yet)
        return $this->getFixtureNames("$os*")
            ->filter(fn ($name) => $name === $os || ModuleTestHelper::extractVariant($name)[0] === $os)
            ->values()
            ->all();
    }

    /**
     * Fixture names in tests/snmpsim without extension or @context
     *
     * @return Collection<int, string>
     */
    private function getFixtureNames(string $pattern = '*'): Collection
    {
        return collect(glob(base_path("tests/snmpsim/$pattern.snmprec")))
            ->map(fn ($file) => explode('@', basename($file, '.snmprec'), 2)[0])
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Variants of the existing fixtures, optionally for one OS
     *
     * @return array<int, string>
     */
    private function getFixtureVariants(?string $os = null): array
    {
        $os = $os !== null && $this->isValidOsName($os) ? $os : null;

        return $this->getFixtureNames($os !== null ? "{$os}_*" : '*')
            ->map(fn ($name) => ModuleTestHelper::extractVariant($name))
            ->filter(fn ($parts) => ($os === null || $parts[0] === $os) && $parts[1] !== '')
            ->pluck(1)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function moduleNames(): array
    {
        return array_values(array_unique(array_merge(
            array_keys(LibrenmsConfig::get('discovery_modules', [])),
            array_keys(LibrenmsConfig::get('poller_modules', [])),
        )));
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
            ->filter(fn ($value) => str_starts_with((string) $value, $partial))
            ->map(fn ($value) => $prefix . $value)
            ->sort()
            ->values();
    }
}
