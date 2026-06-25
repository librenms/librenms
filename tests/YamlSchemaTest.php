<?php

/**
 * YamlSchemaTest.php
 *
 * Verifies yaml files conform to the schema definitions
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <librenms+n@laf.io>
 */

namespace LibreNMS\Tests;

use Illuminate\Support\Str;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Exception\JsonDecodingException;
use JsonSchema\Exception\ValidationException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

#[Group('yaml')]
final class YamlSchemaTest extends TestCase
{
    private array $excluded = [
        '/os_detection/default.yaml',
        '/os_detection/generic.yaml',
        '/os_detection/ping.yaml',
    ];

    public function testConfigSchema(): void
    {
        $error = $this->validateFileAgainstSchema(resource_path('definitions/config_definitions.json'), resource_path('definitions/schema/config_schema.json'));

        $this->assertNull($error, (string) $error);
    }

    #[TestDox('OS definition schema')]
    public function testOSDefinitionSchema(): void
    {
        $this->validateYamlFilesAgainstSchema(resource_path('definitions/os_detection'), resource_path('definitions/schema/os_schema.json'));
    }

    #[TestDox('OS schema module enums stay in sync with config_definitions.json')]
    public function testOSSchemaModuleEnumsInSync(): void
    {
        $definitions = json_decode((string) file_get_contents(resource_path('definitions/config_definitions.json')), true)['config'];
        $schema = json_decode((string) file_get_contents(resource_path('definitions/schema/os_schema.json')), true);

        // module keys actually used by OS definition yaml files: these legacy names must
        // remain valid in the schema even if they are not (or no longer) real modules.
        $used = ['poller_modules' => [], 'discovery_modules' => []];
        foreach (glob(resource_path('definitions/os_detection') . '/*.yaml') as $file) {
            $data = Yaml::parseFile($file) ?: [];
            foreach (array_keys($used) as $section) {
                $used[$section] = array_merge($used[$section], array_keys($data[$section] ?? []));
            }
        }

        foreach (['poller_modules', 'discovery_modules'] as $section) {
            $expected = collect(array_keys($definitions))
                ->filter(fn ($key) => Str::startsWith($key, "$section."))
                ->map(fn ($key) => Str::after($key, "$section."))
                ->merge($used[$section])
                ->unique()->sort()->values()->all();

            $actual = collect(array_keys($schema['properties'][$section]['properties'] ?? []))
                ->sort()->values()->all();

            $this->assertEquals(
                $expected,
                $actual,
                "os_schema.json '$section' enum is out of sync.\n"
                . 'Missing (real module or used by an OS yaml): ' . (implode(', ', array_diff($expected, $actual)) ?: '(none)') . "\n"
                . 'Stale (in schema, not a real module and unused by any OS yaml): ' . (implode(', ', array_diff($actual, $expected)) ?: '(none)')
            );
        }
    }

    #[TestDox('OS match filename')]
    public function testOSMatchFilename(): void
    {
        foreach ($this->listFiles(resource_path('definitions/os_detection/*.yaml')) as $filename => $file) {
            $this->assertEquals(
                Yaml::parseFile($file)['os'],
                substr((string) $filename, 0, -5),
                "Parameter 'os' doesn't match the filename $filename"
            );
        }
    }

    public function testDiscoveryDefinitionSchema(): void
    {
        $this->validateYamlFilesAgainstSchema(resource_path('definitions/os_discovery'), resource_path('definitions/schema/discovery_schema.json'));
    }

    private function validateYamlFilesAgainstSchema(string $dir, string $schema_file): void
    {
        $errors = [];

        foreach ($this->listFiles($dir . '/*.yaml') as $file) {
            $error = $this->validateFileAgainstSchema($file, $schema_file);
            if ($error) {
                $errors[] = $error;
            }
        }

        $count = count($errors);
        $this->assertEmpty($errors, implode("\n", $errors) . "\nFiles with errors: $count\n\n");
    }

    private function listFiles($pattern): array
    {
        return collect(glob($pattern))
            ->reduce(function ($array, $file) {
                if (Str::contains($file, $this->excluded)) {
                    return $array;
                }

                $name = basename($file);
                $array[$name] = $file;

                return $array;
            }, []);
    }

    /**
     * @param  string  $filePath
     * @param  string  $schema_file  full path
     */
    private function validateFileAgainstSchema(string $filePath, string $schema_file): ?string
    {
        $schema = (object) ['$ref' => 'file://' . $schema_file];
        $filename = basename($filePath);

        try {
            $data = str_ends_with($filePath, '.json')
            ? json_decode(file_get_contents($filePath))
            : Yaml::parse(file_get_contents($filePath));
        } catch (ParseException $e) {
            throw new ExpectationFailedException("$filePath Could not be parsed", null, $e);
        }

        try {
            $validator = new \JsonSchema\Validator;
            $validator->validate(
                $data,
                $schema,
                Constraint::CHECK_MODE_TYPE_CAST | Constraint::CHECK_MODE_VALIDATE_SCHEMA | Constraint::CHECK_MODE_EXCEPTIONS
            );
        } catch (JsonDecodingException|ValidationException $e) {
            // Output the filename so we know what file failed
            $error = $e->getMessage();
            if (str_contains($error, 'Error validating /discovery/')) {
                $error = 'Discovery must contain an identifier sysObjectID or sysDescr';
            }

            return "$filename failed to validate against $schema_file\n\n$error";
        }

        if (! $validator->isValid()) {
            $errors = collect($validator->getErrors())
                ->reduce(fn ($out, $error) => sprintf("%s[%s] %s\n", $out, $error['property'], $error['message']), '');

            return "$filename failed to validate against $schema_file\n\n$errors";
        }

        return null;
    }
}
