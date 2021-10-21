<?php
/*
 * ValidationTest.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use Http;
use LibreNMS\Config;
use LibreNMS\Tests\TestCase;
use LibreNMS\ValidationResult;
use LibreNMS\Validator;

class ValidationTest extends TestCase
{
    public function testConfigurationValidation(): void
    {
        config(['app.debug' => true]);
        $results = $this->validateGroup('configuration');

        $this->assertCount(2, $results, var_export($results, true));

        $this->assertEquals('Debug enabled.  This is a security risk.', $results[0]->getMessage());
        $this->assertEquals(ValidationResult::WARNING, $results[0]->getStatus());

        $this->assertEquals('You have no devices.', $results[1]->getMessage());
        $this->assertEquals(ValidationResult::WARNING, $results[1]->getStatus());

        config(['app.debug' => false]);
    }

    public function testDatabaseValidation(): void
    {
        self::dbRequired();

        $results = $this->validateGroup('database');

        $this->assertCount(1, $results, 'something' . var_export($results, true));

        $this->assertEquals(ValidationResult::SUCCESS, $results[0]->getStatus());
    }

    public function testDependenciesValidation(): void
    {
        $results = $this->validateGroup('dependencies');

        $this->assertCount(2, $results, var_export($results, true));

        $this->assertEquals(ValidationResult::SUCCESS, $results[0]->getStatus());
        $this->assertEquals(ValidationResult::SUCCESS, $results[1]->getStatus());
    }

    public function testDiskValidation(): void
    {
        $results = $this->validateGroup('disk');

        $this->assertCount(0, $results, var_export($results, true));
    }

    public function testDistributedPollerValidation(): void
    {
        Config::set('distributed_poller', true);
        $results = $this->validateGroup('distributedpoller');
        $this->assertCount(3, $results, var_export($results, true));
        $this->assertEquals(ValidationResult::FAILURE, $results[0]->getStatus());
        $this->assertEquals(ValidationResult::INFO, $results[1]->getStatus());
        $this->assertEquals(ValidationResult::FAILURE, $results[2]->getStatus());

        Config::set('distributed_poller', false);
        $results = $this->validateGroup('distributedpoller');

        $this->assertCount(1, $results, var_export($results, true));
        $this->assertEquals(ValidationResult::FAILURE, $results[0]->getStatus());
    }

    public function testMailValidation(): void
    {
        $results = $this->validateGroup('mail');

        $this->assertCount(0, $results, var_export($results, true));
    }

    public function testPhpValidation(): void
    {
        $results = $this->validateGroup('php');

        $this->assertCount(0, $results, var_export($results, true));
    }

    public function testProgramsValidation(): void
    {
        $results = $this->validateGroup('programs');

        $this->assertCount(0, $results, var_export($results, true));
    }

    public function testPythonValidation(): void
    {
        $results = $this->validateGroup('python');

        $this->assertCount(0, $results, var_export($results, true));
    }

    public function testRrdValidation(): void
    {
        $results = $this->validateGroup('rrd');

        $this->assertCount(0, $results, var_export($results, true));
    }

    public function testRrdCheckValidation(): void
    {
        $this->assertTrue(true, 'Skipped');
//        $results = $this->validateGroup('rrdcheck');
//
//        $this->assertCount(0, $results, var_export($results, true));
    }

    public function testSystemValidation(): void
    {
        $results = $this->validateGroup('system');

        $this->assertCount(0, $results, var_export($results, true));
    }

    public function testUpdatesValidation(): void
    {
        // github api call is slow, fake it
        Http::fake([
            Config::get('github_api') . 'commits/master' => Http::response([
                'sha' => 'incorrect sha',
            ]),
        ]);

        $results = $this->validateGroup('updates');

        $this->assertIsArray($results);
        $this->assertLessThan(3, count($results), var_export($results, true));
        // all should be warning only
        foreach ($results as $result) {
            $this->assertEquals(ValidationResult::WARNING, $result->getStatus());
        }
    }

    public function testUserValidation(): void
    {
        $results = $this->validateGroup('user');

        $this->assertIsArray($results);
        $this->assertLessThan(2, count($results), var_export($results, true));
    }

    /**
     * @param  string  $group
     * @return \LibreNMS\ValidationResult[]
     */
    private function validateGroup(string $group): array
    {
        $validator = new Validator(true);
        $validator->validate([$group]);

        return $validator->getResults($group);
    }
}
