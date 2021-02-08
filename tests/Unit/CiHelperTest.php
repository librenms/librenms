<?php
/**
 * CiHelperTest.php
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

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\CiHelper;

class CiHelperTest extends TestCase
{
    public function testSetFlags()
    {
        $helper = new CiHelper();
        $allFalse = array_map(function ($flag) {
            return false;
        }, $this->getDefaultFlags());
        $allTrue = array_map(function ($flag) {
            return false;
        }, $this->getDefaultFlags());

        $helper->setFlags($allFalse);
        $this->assertEquals($allFalse, $helper->getFlags());

        $helper->setFlags($allTrue);
        $this->assertEquals($allTrue, $helper->getFlags());

        $helper->setFlags(['undefined_flag' => false]);
        $this->assertEquals($allTrue, $helper->getFlags());

        $helper->setFlags(['full' => false]);
        $testOne = $allTrue;
        $testOne['full'] = false;
        $this->assertEquals($testOne, $helper->getFlags());
    }

    public function testDefaults()
    {
        $helper = new CiHelper();
        $this->assertEquals($this->getDefaultFlags(), $helper->getFlags());
    }

    public function testNoFiles()
    {
        putenv('FILES=none');
        $helper = new CiHelper();
        $helper->detectChangedFiles();
        $this->assertFlagsSet($helper, [
            'lint_skip' => true,
            'style_skip' => true,
            'unit_skip' => true,
            'web_skip' => true,
            'lint_skip_php' => true,
            'lint_skip_python' => true,
            'lint_skip_bash' => true,
        ]);
    }

    public function testSetOs()
    {
        $helper = new CiHelper();
        $helper->setOS(['netonix', 'e3meter']);
        $this->assertFlagsSet($helper, [
            'unit_os' => true,
        ]);

        putenv('FILES=none');
        $helper = new CiHelper();
        $helper->setOS(['netonix']);
        $helper->detectChangedFiles();
        $this->assertFlagsSet($helper, [
            'lint_skip' => true,
            'style_skip' => true,
            'web_skip' => true,
            'unit_os' => true,
            'lint_skip_php' => true,
            'lint_skip_python' => true,
            'lint_skip_bash' => true,
        ]);

        putenv('FILES=includes/definitions/ios.yaml tests/data/fxos.json');
        $helper = new CiHelper();
        $helper->detectChangedFiles();
        $this->assertFlagsSet($helper, [
            'lint_skip' => true,
            'style_skip' => true,
            'web_skip' => true,
            'unit_os' => true,
            'lint_skip_php' => true,
            'lint_skip_python' => true,
            'lint_skip_bash' => true,
        ]);
    }

    public function testSetModules()
    {
        $helper = new CiHelper();
        $helper->setModules(['sensors', 'processors']);
        $this->assertFlagsSet($helper, [
            'unit_modules' => true,
        ]);

        putenv('FILES=none');
        $helper = new CiHelper();
        $helper->setModules(['os']);
        $helper->detectChangedFiles();
        $this->assertFlagsSet($helper, [
            'lint_skip' => true,
            'style_skip' => true,
            'web_skip' => true,
            'unit_modules' => true,
            'lint_skip_php' => true,
            'lint_skip_python' => true,
            'lint_skip_bash' => true,
        ]);

        putenv('FILES=none');
        $helper = new CiHelper();
        $helper->setOS(['linux']);
        $helper->setModules(['os']);
        $helper->detectChangedFiles();
        $this->assertFlagsSet($helper, [
            'lint_skip' => true,
            'style_skip' => true,
            'web_skip' => true,
            'unit_os' => true,
            'unit_modules' => true,
            'lint_skip_php' => true,
            'lint_skip_python' => true,
            'lint_skip_bash' => true,
        ]);

        putenv('FILES=includes/definitions/ios.yaml tests/data/fxos.json');
        $helper = new CiHelper();
        $helper->detectChangedFiles();
        $this->assertFlagsSet($helper, [
            'lint_skip' => true,
            'style_skip' => true,
            'web_skip' => true,
            'unit_os' => true,
            'lint_skip_php' => true,
            'lint_skip_python' => true,
            'lint_skip_bash' => true,
        ]);

        putenv('FILES=includes/definitions/ios.yaml tests/data/fxos.json');
        $helper = new CiHelper();
        $helper->detectChangedFiles();
        $this->assertFlagsSet($helper, [
            'lint_skip' => true,
            'style_skip' => true,
            'web_skip' => true,
            'unit_os' => true,
            'lint_skip_php' => true,
            'lint_skip_python' => true,
            'lint_skip_bash' => true,
        ]);
    }

    public function testFileCategorization()
    {
        putenv('FILES=LibreNMS/Alert/Transport/Sensu.php includes/services.inc.php');
        $helper = new CiHelper();
        $helper->detectChangedFiles();
        $this->assertFlagsSet($helper, [
            'lint_skip_python' => true,
            'lint_skip_bash' => true,
        ]);

        putenv('FILES=/daily.sh includes/services.inc.php');
        $helper = new CiHelper();
        $helper->detectChangedFiles();

        $this->assertFlagsSet($helper, [
            'lint_skip_python' => true,
        ]);

        putenv('FILES=daily.sh LibreNMS/__init__.py');
        $helper = new CiHelper();
        $helper->detectChangedFiles();
        $this->assertFlagsSet($helper, [
            'style_skip' => true,
            'unit_skip' => true,
            'web_skip' => true,
            'lint_skip_php' => true,
        ]);

        putenv('FILES=includes/polling/sensors/ios.inc.php');
        $helper = new CiHelper();
        $helper->detectChangedFiles();
        $this->assertFlagsSet($helper, [
            'lint_skip_python' => true,
            'lint_skip_bash' => true,
            'unit_os' => true,
        ]);

        putenv('FILES=html/images/os/ios.svg');
        $helper = new CiHelper();
        $helper->detectChangedFiles();
        $this->assertFlagsSet($helper, [
            'lint_skip' => true,
            'style_skip' => true,
            'web_skip' => true,
            'lint_skip_php' => true,
            'lint_skip_python' => true,
            'lint_skip_bash' => true,
            'unit_svg' => true,
        ]);

        putenv('FILES=html/images/os/ios.svg');
        $helper = new CiHelper();
        $helper->detectChangedFiles();
        $this->assertFlagsSet($helper, [
            'lint_skip' => true,
            'style_skip' => true,
            'web_skip' => true,
            'lint_skip_php' => true,
            'lint_skip_python' => true,
            'lint_skip_bash' => true,
            'unit_svg' => true,
        ]);

        putenv('FILES=.github/workflows/test.yml');
        $helper = new CiHelper();
        $helper->detectChangedFiles();
        $this->assertFlagsSet($helper, [
            'full' => true,
        ]);
    }

    private function assertFlagsSet(CiHelper $helper, $flags = [])
    {
        $full = $this->getDefaultFlags();
        foreach ($flags as $name => $value) {
            $full[$name] = $value;
            $this->assertEquals($value, $helper->getFlag($name), "Flag $name incorrect.");
        }

        $this->assertEquals($full, $helper->getFlags());
    }

    private function getDefaultFlags()
    {
        return [
            'lint_enable' => true,
            'style_enable' => true,
            'unit_enable' => true,
            'web_enable' => false,
            'lint_skip' => false,
            'style_skip' => false,
            'unit_skip' => false,
            'web_skip' => false,
            'lint_skip_php' => false,
            'lint_skip_python' => false,
            'lint_skip_bash' => false,
            'unit_os' => false,
            'unit_docs' => false,
            'unit_svg' => false,
            'unit_modules' => false,
            'docs_changed' => false,
            'ci' => false,
            'commands' => false,
            'fail-fast' => false,
            'full' => false,
            'quiet' => false,
        ];
    }
}
