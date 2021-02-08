<?php
/**
 * FileCategorizerTest.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use Illuminate\Support\Arr;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\FileCategorizer;

class FileCategorizerTest extends TestCase
{
    public function testEmptyFiles()
    {
        $cat = new FileCategorizer();
        $this->assertEquals($this->getCategorySkeleton(), $cat->categorize());
    }

    public function testIgnoredFiles()
    {
        $this->assertCategorized([], [
            'docs/Nothing.md',
            'none',
            'includes/something.yaml',
            'html/test.css',
            'falsepythonpy',
            'falsephp',
            'falsebash',
            'resource',
            'vendor/misc/composer.lock',
            '/mibs/3com',
        ]);
    }

    public function testPhpFiles()
    {
        $this->assertCategorized([
            'php' => [
                'includes/polling/sensors.inc.php',
                'misc/test.php',
                'app/Http/Kernel.php',
                'LibreNMS/Modules/Mpls.php',
            ],
        ]);
    }

    public function testDocsFiles()
    {
        $this->assertCategorized([
            'docs' => [
                'doc/CNAME',
                'doc/Developing/Creating-Release.md',
                'mkdocs.yml',
            ],
        ]);
    }

    public function testPython()
    {
        $this->assertCategorized([
            'python' => [
                'python.py',
                'LibreNMS/__init__.py',
            ],
        ]);
    }

    public function testBash()
    {
        $this->assertCategorized([
            'bash' => [
                'daily.sh',
                'scripts/deploy-docs.sh',
            ],
        ]);
    }

    public function testSvg()
    {
        $this->assertCategorized([
            'svg' => [
                'html/images/os/zte.svg',
                'html/images/logos/zyxel.svg',
                'html/svg/403.svg',
            ],
        ]);
    }

    public function testResources()
    {
        $this->assertCategorized([
            'resources' => [
                'resources/js/app.js',
                'resources/js/components/LibrenmsSetting.vue',
                'resources/views/layouts/librenmsv1.blade.php',
            ],
            'php' => [
                'resources/views/layouts/librenmsv1.blade.php',
            ],
        ]);
    }

    public function testOsFiles()
    {
        $this->assertCategorized([
            'os' => ['ftd', '3com', 'adva_fsp150', 'saf-integra-b'],
            'os-files' => [
                'tests/data/ftd.json',
                'tests/data/3com_4200.json',
                'tests/data/adva_fsp150_ge114pro.json',
                'tests/data/saf-integra-b.json',
            ],
        ]);

        $this->assertCategorized([
            'os' => ['ciscowap', 'xos', 'ciscosb', 'linux'],
            'os-files' => [
                'tests/snmpsim/ciscowap.snmprec',
                'tests/snmpsim/xos_x480.snmprec',
                'tests/snmpsim/ciscosb_esw540_8p.snmprec',
                'tests/snmpsim/linux_fbsd-nfs-client-v1.snmprec',
            ],
        ]);

        $this->assertCategorized([
            'os' => ['arris-c4', 'ios'],
            'os-files' => [
                'includes/discovery/sensors/temperature/arris-c4.inc.php',
                'includes/polling/entity-physical/ios.inc.php',
            ],
            'php' => [
                'includes/discovery/sensors/temperature/arris-c4.inc.php',
                'includes/polling/entity-physical/ios.inc.php',
            ],
        ]);

        $this->assertCategorized([
            'os' => ['3com', 'arris-dsr4410md', 'adva_fsp3kr7', 'xirrus_aos'],
            'os-files' => [
                'LibreNMS/OS/ThreeCom.php',
                'LibreNMS/OS/ArrisDsr4410md.php',
                'LibreNMS/OS/AdvaFsp3kr7.php',
                'LibreNMS/OS/XirrusAos.php',
            ],
            'php' => [
                'LibreNMS/OS/ThreeCom.php',
                'LibreNMS/OS/ArrisDsr4410md.php',
                'LibreNMS/OS/AdvaFsp3kr7.php',
                'LibreNMS/OS/XirrusAos.php',
            ],
        ]);

        $this->assertCategorized([
            'os' => ['dlink', 'eltex-olt'],
            'os-files' => [
                'includes/definitions/dlink.yaml',
                'includes/definitions/discovery/eltex-olt.yaml',
            ],
        ]);
    }

    public function testFullChecks()
    {
        $this->assertCategorized(['full-checks' => ['composer.lock']]);
        $this->assertCategorized(['full-checks' => ['.github/workflows/test.yml']], ['other', '.github/workflows/test.yml']);

        $this->assertCategorized([
            'os' => ['3com', 'calix', 'ptp650', 'dd-wrt', 'arista_eos'],
            'os-files' => [
                'tests/data/3com.json',
                'tests/snmpsim/calix.snmprec',
                'LibreNMS/OS/Ptp650.php',
                'includes/definitions/dd-wrt.yaml',
                'includes/definitions/discovery/arista_eos.yaml',
            ],
            'php' => [
                'LibreNMS/OS/Ptp650.php',
            ],
            'full-checks' => [true],
        ], [
            'tests/data/3com.json',
            'tests/snmpsim/calix.snmprec',
            'LibreNMS/OS/Ptp650.php',
            'includes/definitions/dd-wrt.yaml',
            'includes/definitions/discovery/arista_eos.yaml',
        ]);
    }

    private function assertCategorized($expected, $input = null, $message = '')
    {
        $files = $input ?? array_unique(Arr::flatten(Arr::except($expected, ['os']))); // os is a virtual category
        $expected = array_merge($this->getCategorySkeleton(), $expected);

        $this->assertEquals($expected, (new FileCategorizer($files))->categorize(), $message);
    }

    private function getCategorySkeleton()
    {
        return [
            'php' => [],
            'docs' => [],
            'python' => [],
            'bash' => [],
            'svg' => [],
            'resources' => [],
            'full-checks' => [],
            'os-files' => [],
            'os' => [],
        ];
    }
}
