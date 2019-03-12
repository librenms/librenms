<?php
/**
 * YamlTest.php
 *
 * -Description-
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <librenms+n@laf.io>
 */

namespace LibreNMS\Tests;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Exception\JsonDecodingException;
use LibreNMS\Config;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlTest extends TestCase
{
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
    public function testDiscoveryDefinitionSchema()
    {
        $this->validateYamlFilesAgainstSchema('/includes/definitions/discovery', '/misc/discovery_schema.json');
    }

    private function validateYamlFilesAgainstSchema($dir, $schema_file)
    {
        $schema = (object)['$ref' => 'file://' . Config::get('install_dir') . $schema_file];

        foreach ($this->listFiles($dir . '/*.yaml') as $info) {
            list($file, $path) = $info;

            try {
                $data = Yaml::parse(file_get_contents($path));
            } catch (ParseException $e) {
                throw new ExpectationFailedException("$path Could not be parsed", null, $e);
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

            $this->assertTrue($validator->isValid(), "$file does not validate. Violations:\n$errors");
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
                $name = basename($file);
                $array[$name] = [$name, $file];
                return $array;
            }, []);
    }
}
