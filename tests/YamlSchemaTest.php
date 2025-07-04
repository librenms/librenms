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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlSchemaTest extends TestCase
{
    private array $excluded = [
        '/os_detection/default.yaml',
        '/os_detection/generic.yaml',
        '/os_detection/ping.yaml',
    ];

    public function testConfigSchema(): void
    {
        $this->validateFileAgainstSchema(resource_path('definitions/config_definitions.json'), resource_path('definitions/schema/config_schema.json'));
    }

    #[Group('os')]
    public function testOSDefinitionSchema(): void
    {
        $this->validateYamlFilesAgainstSchema(resource_path('definitions/os_detection'), resource_path('definitions/schema/os_schema.json'));
    }

    #[Group('os')]
    public function testOSMatchFilename(): void
    {
        foreach ($this->listFiles(resource_path('definitions/os_detection/*.yaml')) as $filename => $file) {
            $this->assertEquals(
                Yaml::parseFile($file)['os'],
                substr($filename, 0, -5),
                "Parameter 'os' doesn't match the filename $filename"
            );
        }
    }

    #[Group('os')]
    public function testDiscoveryDefinitionSchema(): void
    {
        $this->validateYamlFilesAgainstSchema(resource_path('definitions/os_discovery'), resource_path('definitions/schema/discovery_schema.json'));
    }

    private function validateYamlFilesAgainstSchema(string $dir, string $schema_file): void
    {
        foreach ($this->listFiles($dir . '/*.yaml') as $file) {
            $this->validateFileAgainstSchema($file, $schema_file);
        }
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
    private function validateFileAgainstSchema(string $filePath, string $schema_file): void
    {
        $schema = (object) ['$ref' => 'file://' . $schema_file];
        $filename = basename($filePath);

        try {
            $data = Str::endsWith($filePath, '.json')
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
        } catch (JsonDecodingException $e) {
            // Output the filename so we know what file failed
            echo "Json format invalid in $schema_file\n";
            throw $e;
        }

        $errors = collect($validator->getErrors())
            ->reduce(function ($out, $error) {
                return sprintf("%s[%s] %s\n", $out, $error['property'], $error['message']);
            }, '');

        $this->assertTrue($validator->isValid(), "$filename does not validate. Violations:\n$errors");
    }
}
