<?php
/**
 * CommonFunctionsTest.php
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

include 'includes/discovery/vlans/vlan_functions.inc.php';

class VlanFunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function testQBridgeBits2Indices()
    {
        $bits = "8040 201008040201 ff000000   000000";
        $indices = array(1, 10, 19, 28, 37, 46, 55, 64, 65, 66, 67, 68, 69, 70, 71, 72);

        $this->assertTrue(q_bridge_bits2indices($bits) == $indices);
    }
    public function testHex2Bin()
    {
        $hexstr = "54686973206973206f6e6c79206120746573742e00ff";
        $binstr = "This is only a test.\x00\xff";

        $this->assertTrue(hex2bin($hexstr) === $binstr);
        $this->assertTrue(hex2bin_compat($hexstr) === $binstr);
    }
}
