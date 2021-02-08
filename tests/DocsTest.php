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
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

namespace LibreNMS\Tests;

use Symfony\Component\Yaml\Yaml;

class DocsTest extends TestCase
{
    private $hidden_pages = [
        'API/API-Docs.md',
        'Alerting/Old_Templates.md',
        'Extensions/Alerting.md',
        'Extensions/Email-Alerting.md',
        'Extensions/Graphite.md',
        'Extensions/InfluxDB.md',
        'Extensions/OpenTSDB.md',
        'Extensions/Poller-Service.md',
        'Extensions/Port-Description-Parser.md',
        'Extensions/Prometheus.md',
        'Extensions/RRDCached-Security.md',
        'General/Changelogs/2013.md',
        'General/Changelogs/2014.md',
        'General/Changelogs/2015.md',
        'General/Changelogs/2016.md',
        'General/Changelogs/2017.md',
        'General/Changelogs/2018.md',
        'General/Changelogs/2019.md',
        'General/Contributing.md',
        'General/Credits.md',
        'Installation/Installation-(Debian-Ubuntu).md',
        'Installation/Installation-(RHEL-CentOS).md',
        'Installation/Installation-CentOS-6-Apache-Nginx.md',
        'Installation/Installation-Ubuntu-1404-Apache.md',
        'Installation/Installation-Ubuntu-1404-Lighttpd.md',
        'Installation/Installation-Ubuntu-1404-Nginx.md',
        'Installation/Installation-Ubuntu-1604-Apache.md',
        'Installation/Installation-Ubuntu-1604-Nginx.md',
        'Installation/Installation-Ubuntu-1804-Nginx.md',
        'Installation/Installation-Ubuntu-1804-Apache.md',
        'Installation/Installation-Debian-10-Nginx.md',
        'Installation/Installation-CentOS-7-Nginx.md',
        'Installation/Installation-CentOS-7-Apache.md',
        'Installation/Installing-LibreNMS.md',
        'Support/Support-New-OS.md',
        'Installation/Ubuntu-image.md',
        'Installation/CentOS-image.md',
    ];

    /**
     * @group docs
     */
    public function testDocExist()
    {
        $mkdocs = Yaml::parse(file_get_contents(__DIR__ . '/../mkdocs.yml'));
        $dir = __DIR__ . '/../doc/';
        $files = str_replace($dir, '', rtrim(`find $dir -name '*.md'`));

        // check for missing pages
        collect(explode(PHP_EOL, $files))
            ->diff(collect($mkdocs['nav'])->flatten()->merge($this->hidden_pages)) // grab defined pages and diff
            ->each(function ($missing_doc) {
                $this->fail("The doc $missing_doc doesn't exist in mkdocs.yml, please add it to the relevant section");
            });

        $this->expectNotToPerformAssertions();
    }
}
