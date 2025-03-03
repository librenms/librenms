<?php
/**
 * DocsTest.php
 *
 * Tests for Docs.
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
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

namespace LibreNMS\Tests;

use Symfony\Component\Yaml\Yaml;

class DocsTest extends TestCase
{
    private $hidden_pages = [
    ];

    /**
     * @group docs
     */
    public function testDocExist(): void
    {
        $mkdocs = Yaml::parse(file_get_contents(__DIR__ . '/../mkdocs.yml'));
        $dir = __DIR__ . '/../doc/';

        // Define paths to exclude
        $exclude_paths = [
            '*/Extensions/Applications/*',
            '*/General/Changelogs/*',
        ];

        // Build the exclusion part of the find command
        $exclude_conditions = implode(' -not -path ', array_map(fn ($path) => escapeshellarg($path), $exclude_paths));
        $find_command = "find $dir -name '*.md' -not -path $exclude_conditions";

        // Run the find command with exclusions
        $files = str_replace($dir, '', rtrim(`$find_command`));

        // Check for missing pages
        collect(explode(PHP_EOL, $files))
            ->diff(collect($mkdocs['nav'])->flatten()->merge($this->hidden_pages)) // grab defined pages and diff
            ->each(function ($missing_doc) {
                $this->fail("The doc $missing_doc doesn't exist in mkdocs.yml, please add it to the relevant section");
            });

        $this->expectNotToPerformAssertions();
    }
}
