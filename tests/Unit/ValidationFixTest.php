<?php
/*
 * ValidationFixTest.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use LibreNMS\Tests\TestCase;
use LibreNMS\Validations\Rrd\CheckRrdVersion;
use Storage;

class ValidationFixTest extends TestCase
{
    public function testRrdVersionFix(): void
    {
        Storage::fake('base');
        Storage::disk('base')->put('config.php', <<<'EOF'
<?php
$config['test'] = 'rrdtool_version';
$config['rrdtool_version'] = '1.0';
$config["rrdtool_version"] = '1.1';
# comment

EOF
        );

        (new CheckRrdVersion())->fix();

        $actual = Storage::disk('base')->get('config.php');
        $this->assertSame(<<<'EOF'
<?php
$config['test'] = 'rrdtool_version';
# comment

EOF, $actual);
        Storage::disk('base')->delete('config.php');
    }
}
