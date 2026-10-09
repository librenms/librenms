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

namespace LibreNMS\Tests\Unit;

use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

final class DocsTest extends TestCase
{
    private $hidden_pages = [
    ];

    #[Group('docs')]
    public function testDocExist(): void
    {
        $mkdocs = Yaml::parse(file_get_contents(self::basePath('mkdocs.yml')));

        // Paths to exclude
        $exclude_paths = [
            'Extensions/Applications/',
            'General/Changelogs/',
            'Alerting/Transports/',
            'Developing/Design/',
        ];

        // Check for missing pages
        collect(Finder::create()->files()->in(self::basePath('doc'))->name('*.md')->notPath($exclude_paths))
            ->map(fn ($file) => $file->getRelativePathname())
            ->diff(collect($mkdocs['nav'])->flatten()->merge($this->hidden_pages)) // grab defined pages and diff
            ->each(function ($missing_doc): void {
                $this->fail("The doc $missing_doc doesn't exist in mkdocs.yml, please add it to the relevant section");
            });

        $this->expectNotToPerformAssertions();
    }
}
