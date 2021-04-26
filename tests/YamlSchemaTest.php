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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <librenms+n@laf.io>
 */

namespace LibreNMS\Tests;

use Illuminate\Support\Str;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Exception\JsonDecodingException;
use LibreNMS\Config;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlSchemaTest extends TestCase
{
    private $excluded = [
        '/includes/definitions/default.yaml',
        '/includes/definitions/generic.yaml',
        '/includes/definitions/ping.yaml',
    ];

    public function testConfigSchema()
    {
        $this->validateFileAgainstSchema('/misc/config_definitions.json', '/misc/config_schema.json');
    }

    /**
     * @group os
     */
    public function testOSDefinitionSchema()
    {
        $this->validateYamlFilesAgainstSchema('/includes/definitions', '/misc/os_schema.json');
    }

    /**
     * @group os
     */
    public function testOSMatchFilename()
    {
        foreach ($this->listFiles('/includes/definitions/*.yaml') as $filename => $file) {
            $this->assertEquals(
                Yaml::parseFile($file)['os'],
                substr($filename, 0, -5),
                "Parameter 'os' doesn't match the filename $filename"
            );
        }
    }

    /**
     * @group os
     */
    public function testDiscoveryDefinitionSchema()
    {
        $this->validateYamlFilesAgainstSchema('/includes/definitions/discovery', '/misc/discovery_schema.json');
    }

    private function validateYamlFilesAgainstSchema($dir, $schema_file)
    {
        foreach ($this->listFiles($dir . '/*.yaml') as $file) {
            $this->validateFileAgainstSchema($file, $schema_file);
        }
    }

    public function listOsDefinitionFiles()
    {
        return $this->listFiles('/includes/definitions/*.yaml');
    }

    public function listDiscoveryFiles()
    {
        return $this->listFiles('/includes/definitions/discovery/*.yaml');
    }

    private function listFiles($pattern)
    {
        $pattern = Config::get('install_dir') . $pattern;

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
     * @param string $filePath
     * @param string $schema_file
     */
    private function validateFileAgainstSchema($filePath, $schema_file)
    {
        $schema = (object) ['$ref' => 'file://' . Config::get('install_dir') . $schema_file];
        $filename = basename($filePath);
        $filePath = Str::start($filePath, Config::get('install_dir'));

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
                Constraint::CHECK_MODE_TYPE_CAST  // | Constraint::CHECK_MODE_VALIDATE_SCHEMA
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
