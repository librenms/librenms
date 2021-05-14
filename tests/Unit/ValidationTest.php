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
    public function testConfigurationValidation()
    {
        $results = $this->validateGroup('configuration');

        $this->assertCount(1, $results);
        $this->assertEquals(ValidationResult::WARNING, $results[0]->getStatus());
    }

    public function testDatabaseValidation()
    {
        $results = $this->validateGroup('database');

        $this->assertCount(1, $results);
        $this->assertEquals(ValidationResult::SUCCESS, $results[0]->getStatus());
    }

    public function testDependenciesValidation()
    {
        $results = $this->validateGroup('dependencies');

        $this->assertCount(2, $results);
        $this->assertEquals(ValidationResult::SUCCESS, $results[0]->getStatus());
        $this->assertEquals(ValidationResult::SUCCESS, $results[1]->getStatus());
    }

    public function testDiskValidation()
    {
        $results = $this->validateGroup('disk');

        $this->assertCount(0, $results);
    }

    public function testDistributedPollerValidation()
    {
        Config::set('distributed_poller', true);
        $results = $this->validateGroup('distributedpoller');
        $this->assertCount(3, $results);
        $this->assertEquals(ValidationResult::FAILURE, $results[0]->getStatus());
        $this->assertEquals(ValidationResult::INFO, $results[1]->getStatus());
        $this->assertEquals(ValidationResult::FAILURE, $results[2]->getStatus());

        Config::set('distributed_poller', false);
        $results = $this->validateGroup('distributedpoller');

        $this->assertCount(1, $results);
        $this->assertEquals(ValidationResult::FAILURE, $results[0]->getStatus());
    }

    public function testMailValidation()
    {
        $results = $this->validateGroup('mail');

        $this->assertCount(0, $results);
    }

    public function testPhpValidation()
    {
        $results = $this->validateGroup('php');

        $this->assertCount(0, $results);
    }

    public function testProgramsValidation()
    {
        $results = $this->validateGroup('programs');

        $this->assertCount(0, $results);
    }

    public function testPythonValidation()
    {
        $results = $this->validateGroup('python');

        $this->assertCount(0, $results);
    }

    public function testRrdValidation()
    {
        $results = $this->validateGroup('rrd');

        $this->assertCount(0, $results);
    }

    public function testRrdCheckValidation()
    {
        $this->assertTrue(true, 'Skipped');
//        $results = $this->validateGroup('rrdcheck');
//
//        $this->assertCount(0, $results);
    }

    public function testSystemValidation()
    {
        $results = $this->validateGroup('system');

        $this->assertCount(0, $results);
    }

    public function testUpdatesValidation()
    {
        // github api call is slow, fake it
        Http::fake([
            Config::get('github_api') . 'commits/master' => Http::response([
                'sha' => 'incorrect sha',
            ]),
        ]);

        $results = $this->validateGroup('updates');

        $this->assertIsArray($results);
        $this->assertLessThan(3, count($results));
        // all should be warning only
        foreach ($results as $result) {
            $this->assertEquals(ValidationResult::WARNING, $result->getStatus());
        }
    }

    public function testUserValidation()
    {
        $results = $this->validateGroup('user');

        $this->assertIsArray($results);
        $this->assertLessThan(2, count($results));
    }

    private function validateGroup($group)
    {
        $validator = new Validator(true);
        $validator->validate([$group]);

        return $validator->getResults($group);
    }
}
